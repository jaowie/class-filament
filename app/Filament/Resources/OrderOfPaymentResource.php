<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderOfPaymentResource\Pages;
use App\Filament\Resources\OrderOfPaymentResource\RelationManagers;
use App\Models\OrderOfPayment;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('account_codes')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->color(fn (string $state): string => match ($state) {
                        'For Posting' => 'gray',
                        'Posted' => 'info', // Filament's blue color
                        'Paid' => 'success', // Filament's green color
                        default => 'gray', // fallback
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'For Posting' => 'heroicon-o-clock',
                        'Posted' => 'heroicon-o-check-circle',
                        'Paid' => 'heroicon-o-check-badge',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('purpose')
                    ->searchable(),
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
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(null)
            ->actions([
                // Tables\Actions\EditAction::make(),
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
