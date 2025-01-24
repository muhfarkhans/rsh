<?php

namespace App\Filament\Resources\MasterData;

use App\Filament\Resources\MasterData\ServiceResource\Pages;
use App\Filament\Resources\MasterData\ServiceResource\RelationManagers;
use App\Helpers\Helper;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

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
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Durasi (menit)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commision')
                    ->label('Komisi')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListServices::route('/'),
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
                ->label('Nama')
                ->placeholder('Isi nama layanan')
                ->required()
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Harga')
                ->hint('Isi harga layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columns(1),
            TextInput::make('commision')
                ->label('Komisi')
                ->hint('Isi jumlah komisi layanan')
                ->required()
                ->numeric()
                ->prefix('Rp. ')
                ->columns(1),
            TextInput::make('duration')
                ->label('Durasi')
                ->hint('Isi durasi layanan dalam menit')
                ->required()
                ->numeric()
                ->suffix('Menit')
                ->columns(1),
        ];
    }
}
