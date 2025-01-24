<?php

namespace App\Filament\Resources\MasterData;

use App\Filament\Resources\MasterData\DiscountResource\Pages;
use App\Filament\Resources\MasterData\DiscountResource\RelationManagers;
use App\Helpers\Helper;
use App\Models\Discount;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Diskon')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return $record->is_active == 1 ? 'success' : 'danger';
                    })
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return (bool) $record->is_active == 1 ? 'Aktif' : 'Tidak Aktif';
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            // 'create' => Pages\CreateDiscount::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Nama diskon')
                ->placeholder('Isi nama diskon')
                ->required()
                ->columnSpanFull(),
            TextInput::make('discount')
                ->label('Harga diskon')
                ->hint('Isi harga layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Diskon aktif?')
                ->default(false)
        ];
    }
}
