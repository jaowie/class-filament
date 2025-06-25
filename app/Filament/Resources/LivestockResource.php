<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LivestockResource\Pages;
use App\Filament\Resources\LivestockResource\RelationManagers;
use App\Models\Livestock;
use App\Models\Owner;
use App\Models\AccountCode;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;// Import Toggle for the switch
use Filament\Forms\Get; 
use Filament\Forms\Set; 
use Filament\Forms\Components\{Select, TextInput, Toggle, Repeater, Actions, DatePicker, TimePicker, Hidden, Grid};
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Placeholder;
use App\Models\Delivery; // Import the Delivery model
use App\Models\OwnerLivestockBatch;
use App\Services\OrderOfPaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Str;
use Filament\Tables\Grouping\Group;
use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class LivestockResource extends Resource
{
    protected static ?string $model = Livestock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Livestock Details')
                        ->schema([
                            Select::make('owner_id')
                                ->relationship('owner', 'first_name')
                                ->label('Owner')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('first_name')->required()->maxLength(255),
                                    TextInput::make('middle_name')->maxLength(255),
                                    TextInput::make('last_name')->required()->maxLength(255),
                                    TextInput::make('address')->maxLength(255),
                                ])
                                ->createOptionUsing(fn (array $data): int => \App\Models\Owner::create($data)->id)
                                ->required()
                                ->live() 
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $owner = Owner::find($state);
                                    if ($owner) {
                                        $set('owner_name', $owner->first_name . ' ' . $owner->last_name);
                                    } else {
                                        $set('owner_name', null);
                                    }
                                })
                                ->dehydrated(true), 
                            Hidden::make('owner_name')
                                ->dehydrated(false),

                            Select::make('type')
                                ->required()
                                ->options([
                                    'Hog' => 'Hog',
                                    'Goat' => 'Goat',
                                    'Cattle' => 'Cattle',
                                ])
                                ->native(false)
                                ->searchable()
                                ->dehydrated(true),

                            TextInput::make('quantity')
                                ->label('Quantity of Livestock')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->live()
                                ->dehydrated(true),

                            Select::make('handler_id')
                                ->relationship('handler', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->dehydrated(true),

                            DatePicker::make('date_of_delivery')
                                ->required()
                                ->native(false)
                                ->dehydrated(true)
                                ->live(),

                            TimePicker::make('time_of_delivery')
                                ->required()
                                ->dehydrated(true),

                            // Place of origin
                            TextInput::make('origin')
                                ->label('Place of Origin')
                                ->maxLength(255)
                                ->required()
                                ->dehydrated(true),

                            // Remarks
                            TextInput::make('remarks')
                                ->maxLength(255)
                                ->dehydrated(true),
                        ]),

                    Wizard\Step::make('Confirm Livestock Codes')
                        ->schema([
                            Placeholder::make('generated_livestock_codes')
                                ->label('Generated Livestock Codes')
                                ->content(function (callable $get, callable $set) {
                                    $ownerId = $get('owner_id');
                                    $quantity = $get('quantity');
                                    $dateOfDelivery = $get('date_of_delivery');
                                    $ownerName = $get('owner_name');

                                    if (!$ownerId || !$quantity || !$dateOfDelivery || !$ownerName) {
                                        return 'Please complete the "Livestock Details" step to generate codes.';
                                    }

                                    $codes = self::generateLivestockCodes(
                                        $ownerName,
                                        $quantity,
                                        $dateOfDelivery
                                    );

                                    $set('livestock_codes', $codes);

                                    return view('filament.components.livestock-code-display', ['codes' => $codes]);
                                }),

                            Hidden::make('livestock_codes')
                                ->dehydrated(true),
                        ]),
                ])
                ->skippable(false)
                ->contained()
                ->columns(2)
                ->columnSpanFull()  
                ->submitAction(
                    \Filament\Forms\Components\Actions\Action::make('create')
                        ->label('Create Livestock Entries')
                        ->submit('livestock-details')
                        ->keyBindings(['mod+s'])
                )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('batch')
                    ->label('Batch')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_display')
                    ->label('Status')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $status = $record->status ?? Livestock::where('owner_id', $record->owner_id)
                                                        ->where('batch', $record->batch)
                                                        ->value('status');
                        
                        $count = $record->livestock_count ?? Livestock::where('owner_id', $record->owner_id)
                                                                ->where('batch', $record->batch)
                                                                ->count();
                        
                        if (empty($status)) return '';
                        
                        $colors = [
                            'received' => ['bg' => '#3b82f6', 'text' => '#ffffff'],
                            'OP Generated' => ['bg' => '#eab308', 'text' => '#000000'],
                        ];
                        
                        $style = $colors[$status] ?? ['bg' => '#6b7280', 'text' => '#ffffff'];
                        
                        return "
                            <div style='
                                background-color: {$style['bg']};
                                color: {$style['text']};
                                padding: 3px 10px;
                                border-radius: 12px;
                                font-size: 12px;
                                font-weight: 500;
                                display: inline-block;
                                box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                            '>
                                {$status} <span style='font-weight: bold'>{$count}</span>
                            </div>
                        ";
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered At')
                    ->date() 
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime()
                    ->sortable(),
            ])
                ->actions([
                    Tables\Actions\Action::make('view_batch')
                        ->label('View Batch')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => static::getUrl('view-livestock-batch', [
                            'owner' => $record->owner->uuid,
                            'batch' => $record->batch,
                        ])),
                    Tables\Actions\Action::make('add_order_of_payment')
                        ->label('Add Order of Payment')
                        ->icon('heroicon-o-document-plus')
                        ->modalHeading('Add Order of Payment')
                            ->closeModalByClickingAway(false)
                        ->form([
                            Forms\Components\ToggleButtons::make('purpose')
                                ->label('Purpose')
                                ->options([
                                    'slaughter' => 'For Slaughter',
                                    'others' => 'Others',
                                ])
                                ->default('slaughter') 
                                ->inline()   // puts them side-by-side
                                ->required(),

                            Select::make('account_codes')
                                ->label('Account Codes')
                                ->multiple()
                                ->searchable()
                                ->options(
                                    AccountCode::all()->pluck('description', 'account_code')
                                )
                                ->required(),
                            Forms\Components\Textarea::make('remarks'),
                        ])
                        ->action(function (array $data, $record, $action) {
                            $livestock = Livestock::where('owner_id', $record->owner_id)
                                    ->where('batch', $record->batch)
                                    ->get()
                                    ->map(fn ($item) => [
                                        'id' => $item->id,
                                        'type' => $item->type,
                                        'batch' => $item->batch,
                                    ])
                                    ->toArray();

                                // Format account codes (id => description)
                            $accountCodes = collect($data['account_codes'])->mapWithKeys(function ($code) {
                                $code = AccountCode::where('account_code', $code)->first(); // Fetch the actual model
                                
                                return [$code->account_code => $code->description];
                            })->toArray();

                            // Build final payload
                            $payload = (object) [
                                'account_codes' => $accountCodes,
                                'owner_id' => $record->owner_id,
                                'purpose' => $data['purpose'],
                                'livestock' => $livestock,
                                'remarks' => $data['remarks'] ?? null,
                            ];

                            $OPReview = app(\App\Services\OrderOfPaymentService::class);
                            $reviewData = $OPReview->reviewOP($payload);

                           // Store the payload in the action for later use
                            $action->payload = $payload;

                            // Open modal with preview and confirmation button
                            $action->modalContent(view('filament.components.op-preview', [
                                'data' => $reviewData
                            ])->render());

                            $action->modalSubmitActionLabel('Confirm and Create Order of Payment')
                                ->modalWidth('4xl')
                                ->modalHeading('Order of Payment Preview')
                                ->extraModalFooterActions([
                                    Action::make('cancel')
                                        ->color('gray')
                                        ->label('Cancel')
                                        ->cancel()
                                ]);
                        })
                        ->modalSubmitAction(fn (Action $action) => 
                            Action::make('confirm')
                                ->label('Confirm and Create Order of Payment')
                                ->action(function () use ($action) {
                                    dd(1);
                                    $OPReview = app(\App\Services\OrderOfPaymentService::class);
                                    // $result = $OPReview->processOP($action->payload);
                                    
                                    if ($result) {
                                        Notification::make()
                                            ->title('Order of Payment created successfully')
                                            ->success()
                                            ->send();
                                        
                                        // Optionally redirect
                                        return redirect()->route('filament.resources.order-of-payments.index');
                                    } else {
                                        Notification::make()
                                            ->title('Failed to create Order of Payment')
                                            ->danger()
                                            ->send();
                                    }
                                })
                        )
                ]);
    }

    public static function getEloquentQuery(): Builder
    {
        try {
            return parent::getEloquentQuery()
                ->join('deliveries', 'livestocks.delivery_id', '=', 'deliveries.id')
                ->select([
                    'owner_id',
                    'batch',
                    DB::raw('MIN(livestocks.id) as id'),
                    DB::raw('COUNT(*) as livestock_count'),
                    DB::raw('MIN(livestocks.type) as type'),
                    DB::raw('MIN(livestocks.created_at) as created_at'),
                    DB::raw('MIN(deliveries.delivered_at) as delivered_at'),
                    
                ])
                ->with(['owner'])
                ->groupBy(['owner_id', 'batch'])
                ->orderBy('delivered_at', 'desc');
        } catch (\Throwable $e) {
            report($e); 
            return Livestock::query()->whereRaw('0 = 1');
        }
    }

    public static function resolveRecord($key): Model
    {
        return static::getEloquentQuery()->where('livestocks.id', $key)->firstOrFail();
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
            'index' => Pages\ListLivestocks::route('/'),
            'create' => Pages\CreateLivestock::route('/create'),
            'view-livestock-batch' => Pages\ViewLivestockBatch::route('/view-batch/{owner}/{batch}'),
        ];
    }

    public static function generateLivestockCodes(string $ownerFullName, int $quantity, string $dateOfDelivery): array
    {
        $codes = [];

        $ownerInitials = Str::of($ownerFullName)
                            ->split('/\s+/')
                            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
                            ->implode('');

        $formattedDate = Carbon::parse($dateOfDelivery)->format('Ymd');

        for ($i = 1; $i <= $quantity; $i++) {
            $codes[] = "{$ownerInitials}-{$formattedDate}-" . str_pad($i, 3, '0', STR_PAD_LEFT);
        }

        return $codes;
    }
    
}
