<?php

namespace App\Filament\Resources;

use App\Enums\OrderTypeEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 4;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Order Details')
                        ->schema([
                            Forms\Components\TextInput::make('order_number')
                                ->label('Order Number')
                                ->default("OR-" . random_int(1000000, 9999999))
                                ->required()
                                ->disabled()
                                ->dehydrated(),
                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')
                                ->required()
                                ->searchable()
                                ->label('Customer Name'),
                           
                            Forms\Components\Select::make('status')
                                ->required()
                                ->label('Status')
                                ->options([
                                    'pending' => OrderTypeEnum::PINDING->value,
                                    'completed' => OrderTypeEnum::COMPLETED->value,
                                    'declined' => OrderTypeEnum::DECLINED->value,
                                    'processing' => OrderTypeEnum::PROCESSING->value,
                                ])
                                ->columnSpanFull(),
                            Forms\Components\MarkdownEditor::make('notes')
                                ->label('Notes')
                                ->placeholder('Enter notes here')
                                ->columnSpanFull()
                        ])->columns(2),
                    Step::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship('items')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->required()
                                        ->minValue(1)
                                        ->numeric()
                                        ->default(1),
                                    Forms\Components\TextInput::make('unit_price')
                                        ->label('Unit Price')
                                        ->disabled()
                                        ->dehydrated()
                                        ->numeric()
                                        ->required(),
                                ])->columns(3)
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                ->searchable()
                ->toggleable()    
                ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->label('Total Price')
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money()
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
