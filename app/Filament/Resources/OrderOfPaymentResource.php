<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderOfPaymentResource\Pages;
use App\Filament\Resources\OrderOfPaymentResource\RelationManagers;
use App\Models\AccountCode;
use App\Models\Livestock;
use App\Models\OrderOfPayment;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class OrderOfPaymentResource extends Resource
{
    protected static ?string $model = OrderOfPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_of_payment_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Owner')
                        ->searchable([
                            'owners.first_name',
                            'owners.middle_name',
                            'owners.last_name'
                        ]),
                Tables\Columns\TextColumn::make('batch'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'For Posting' => 'gray',
                        'Posted' => 'info', 
                        'Paid' => 'success', 
                        default => 'gray', 
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'For Posting' => 'heroicon-o-clock',
                        'Posted' => 'heroicon-o-check-circle',
                        'Paid' => 'heroicon-o-check-badge',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('purpose'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('encoder.name')
                    ->label('Encoded By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('remarks')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(null)
        ->actions([
                Tables\Actions\Action::make('postOP')
                    ->label('Post OP')
                    ->button()
                    ->color('primary')
                    ->visible(function ($record) {
                        return auth()->user()->hasAnyRole(['CEE', 'super_admin']) 
                            && $record->status !== 'Posted';
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Post Order of Payment')
                    ->modalDescription('Are you sure you want to mark this OP as posted?')
                    ->modalSubmitActionLabel('Confirm & View PDF')
                    ->action(function ($record) {
                        try {
                            DB::beginTransaction();

                            // 1. First prepare all data without saving
                            $accountDetails = [];
                            $accountCodes = is_array($record->account_codes) 
                                ? $record->account_codes 
                                : json_decode($record->account_codes, true) ?? [];
                            
                            foreach ($accountCodes as $code => $description) {
                                $actualCode = is_numeric($code) ? $description : $code;
                                $account_code = AccountCode::where('account_code', $actualCode)->firstOrFail();
        
                                $livestockType = Livestock::where('order_of_payment_id', $record->id)      
                                                    ->pluck('type')
                                                    ->first();

                                $amount = match ($livestockType) {
                                    'Hog' => $account_code->hog_amount ?? $account_code->amount,
                                    'Cattle' => $account_code->cattle_amount ?? $account_code->amount,
                                    'Goat' => $account_code->goat_amount ?? $account_code->amount,
                                    default => $account_code->amount,
                                };

                                $accountDetails[] = [
                                    'code' => $actualCode,
                                    'description' => $account_code->description,
                                    'amount' => $amount ?? 0,
                                ];
                            }

                            $payload = [
                                'owner_name' => $record->owner->full_name ?? 'N/A',
                                'owner_address' => $record->owner->address ?? 'N/A',
                                'account_code_details' => $accountDetails,
                                'total_amount' => $record->amount,
                                'op_number' => $record->order_of_payment_no,
                                'remarks' => $record->remarks ?? '',
                            ];
        
                            $pdfResponse = app(\App\Services\OrderOfPaymentService::class)
                                ->generatePdfBlade($payload);

                            if (!$pdfResponse) {
                                
                                throw new \Exception('PDF generation failed');
                            }

                            $record->update(['status' => 'Posted']);
                            DB::commit();

                            return $pdfResponse;

                        } catch (\Exception $e) {
                            DB::rollBack();
                            
                            Notification::make()
                                ->title('OP Processing Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                                
                            // Prevent modal from closing on error
                            throw $e;
                        }
                    }),

                    Tables\Actions\Action::make('verify')
                        ->label('Verify')
                        ->button()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->visible(fn ($record): bool => 
                            auth()->user()->hasAnyRole(['CEE', 'super_admin']) && 
                            $record->status === 'Posted'
                        )
                        ->action(function ($record) {
                            // Your verification logic here
                            $record->update(['status' => 'Paid']);
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('OP Posted Successfully')
                                ->success()
                                ->send();
                }),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderOfPayments::route('/'),
            'edit' => Pages\EditOrderOfPayment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Disables creation via policy
    }

}
