<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LivestockResource\Pages;
use App\Models\Livestock;
use App\Models\Owner;
use App\Models\AccountCode;
use App\Models\Handler;
use App\Models\HandlerPlateNumber;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\{Select, TextInput, DatePicker, TimePicker, Hidden, Grid};
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Tables\Actions\Action as ActionsAction;
use Illuminate\Support\Facades\Log;

class LivestockResource extends Resource
{
    protected static ?string $model = Livestock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $initialStep = request()->query('step', 1);
        return $form
            ->schema([
                Placeholder::make('session_summary')
                    ->content(function () {
                        $handlerId = session('handler_id');
                        $plateId = session('handler_plate_number_id');

                        if (!$handlerId || !$plateId) {
                            return '';
                        }

                        $handler = Handler::find($handlerId);
                        $plate = HandlerPlateNumber::find($plateId);

                        if (!$handler || !$plate) {
                            return '';
                        }

                        return "### Handler: {$handler->name} | Plate Number: {$plate->plate_no}";
                    })
            ->columnSpanFull(),
                Wizard::make([
                    Wizard\Step::make('Select Handler & Plate Number')
                        ->schema([
                            Select::make('handler_id')
                                ->label('Handler')
                                ->relationship('handler', 'name')
                                ->default(session('handler_id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state) {
                                    session(['handler_id' => $state]);
                                    // session()->forget('handler_plate_number_id');
                                }),

                            Select::make('handler_plate_number_id')
                                ->label('Plate Number')
                                ->default(session('handler_plate_number_id'))
                                ->options(function (callable $get) {
                                    $handlerId = $get('handler_id');
                                    if (!$handlerId) return [];

                                    return HandlerPlateNumber::where('handler_id', $handlerId)
                                        ->pluck('plate_no', 'id')
                                        ->toArray();
                                })
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('plate_no')->required(),
                                ])
                                ->createOptionUsing(function (array $data, callable $get) {
                                    return HandlerPlateNumber::create([
                                        'plate_no' => $data['plate_no'],
                                        'handler_id' => $get('handler_id'),
                                    ])->id;
                                })
                                ->afterStateUpdated(function ($state) {
                                    session(['handler_plate_number_id' => $state]);
                                }),

                        ]),

                    Wizard\Step::make('Livestock Details')
                        ->schema([
                            Select::make('owner_id')
                                ->relationship('owner', 'first_name')
                                ->label('Owner')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name.' '.$record->last_name . ' - ' . $record->address)
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
                                    $owner = \App\Models\Owner::find($state);
                                    $set('owner_name', $owner?->first_name . ' ' . $owner?->last_name);
                                }),

                            Hidden::make('owner_name')->dehydrated(false),

                            Select::make('type')
                                ->required()
                                ->options([
                                    'Hog' => 'Hog',
                                    'Goat' => 'Goat',
                                    'Cattle' => 'Cattle',
                                ])
                                ->native(false)
                                ->searchable(),

                            TextInput::make('quantity')
                                ->label('Quantity of Livestock')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->live(),

                            DatePicker::make('date_of_delivery')
                                ->required()
                                ->native(false)
                                ->live(),

                            TimePicker::make('time_of_delivery')
                                ->required(),

                            TextInput::make('origin')
                                ->label('Place of Origin')
                                ->maxLength(255)
                                ->required(),

                            TextInput::make('remarks')
                                ->maxLength(255),
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
                        ]),
                ])
                    ->startOnStep($initialStep)
                    ->skippable(false)
                    ->contained()
                    ->columns(2)
                    ->columnSpanFull()
                    ->submitAction(
                        \Filament\Forms\Components\Actions\Action::make('create')
                            ->label('Create Livestock Entries')
                            ->submit('livestock-wizard')
                            ->keyBindings(['mod+s'])
                            ->action('create')
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Owner')
                        ->searchable([
                            'owners.first_name',
                            'owners.middle_name',
                            'owners.last_name'
                        ]),
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
                    ->form([
                        Forms\Components\ToggleButtons::make('purpose')
                            ->label('Purpose')
                            ->options([
                                'slaughter' => 'For Slaughter',
                                'others' => 'Others',
                            ])
                            ->default('slaughter')
                            ->inline()
                            ->required(),
                            
                        Forms\Components\Select::make('account_codes')
                            ->label('Account Codes')
                            ->multiple()
                            ->searchable()
                            ->options(AccountCode::all()->mapWithKeys(function ($item) {
                                return [$item->account_code => "{$item->account_code} - {$item->description}"];
                            }))
                            ->required(),
                            
                        Forms\Components\Textarea::make('remarks')
                            ->label('Additional Remarks')
                            ->columnSpanFull(),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Hidden::make('has_previewed'),
                                Forms\Components\Hidden::make('preview_data'), 
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('preview')
                                        ->label('Preview Order')
                                        ->color('gray')
                                        ->icon('heroicon-o-eye')
                                        ->action(function (Forms\Get $get, $record, Forms\Set $set) {
                                            $accountCodes = collect($get('account_codes'))->mapWithKeys(function ($code) {
                                            $accountCode = AccountCode::where('account_code', $code)->first();
                                            return [
                                                $code => [
                                                    'code' => $code,
                                                    'description' => $accountCode->description
                                                ]
                                            ];
                                        })->toArray();

                                        $payload = (object)[
                                            'account_codes' => $accountCodes,
                                            'owner_id' => $record->owner_id,
                                            'purpose' => $get('purpose'),
                                            'livestock' => Livestock::where('owner_id', $record->owner_id)
                                                ->where('batch', $record->batch)
                                                ->get()
                                                ->map(fn ($item) => [
                                                    'id' => $item->id,
                                                    'type' => $item->type,
                                                    'batch' => $item->batch,
                                                ])
                                                ->toArray(),
                                            'remarks' => $get('remarks'),
                                        ];

                                        $OPReview = app(\App\Services\OrderOfPaymentService::class);
                                        $previewData = $OPReview->reviewOP($payload);

                                        if ($previewData) {
                                            $set('preview_data', $previewData);
                                            $set('show_preview', true);
                                            $set('has_previewed', true);
                                        } else {
                                            Notification::make()
                                                ->title('Failed to generate preview')
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                                ]),
                                // Forms\Components\Toggle::make('show_preview')
                            ])
                        ->columnSpanFull(),
    
                        Forms\Components\Section::make('Order Preview')
                            ->schema([
                                Forms\Components\View::make('filament.components.op-preview')
                                    ->viewData(function (Forms\Get $get) {
                                        return [
                                            'data' => $get('preview_data') 
                                        ];
                                    })
                            ])
                            ->hidden(fn (Forms\Get $get): bool => !$get('show_preview'))
                            ->columnSpanFull(),
                        ])

                ->action(function (array $data, $record, Tables\Actions\Action $action) {
        
                    if (!($data['has_previewed'] ?? false)) {
                        Notification::make()
                            ->title('Please generate preview first')
                            ->warning()
                            ->send();
                        $action->halt();
                        return;
                    }

                    $payload = (object)[
                        'account_codes' => collect($data['account_codes'])->mapWithKeys(function ($code) {
                            $code = AccountCode::where('account_code', $code)->first();
                            return [$code->account_code => $code->description];
                        })->toArray(),
                        'owner_id' => $record->owner_id,
                        'owner_name' => $data['preview_data']->owner_name,
                        'owner_address' => $data['preview_data']->owner_address,
                        'account_code_details' => $data['preview_data']->account_code_details,
                        'purpose' => $data['purpose'],
                        'amount' => $data['preview_data']->total_amount ?? 0,
                        'livestock' => $data['preview_data']->livestock ?? [],
                        'remarks' => $data['remarks'] ?? null,
                    ];
    
                    $OPService = app(\App\Services\OrderOfPaymentService::class);
                    $result = $OPService->postOP($payload);

                    if ($result) {
                        Notification::make()
                            ->title('Order created successfully')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed to create order')
                            ->danger()
                            ->send();
                    }
                })
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

        $milliseconds = substr((string) microtime(true), -3);

        for ($i = 1; $i <= $quantity; $i++) {
            $codes[] = "{$ownerInitials}-{$milliseconds}{$formattedDate}-" . str_pad($i, 3, '0', STR_PAD_LEFT);
        }

        return $codes;
    }
    
}
