<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CuppingResource\Pages;
use App\Filament\App\Resources\CuppingResource\RelationManagers;
use App\Models\ClientVisit;
use App\Models\ClientVisitCupping;
use App\Models\Cupping;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuppingResource extends Resource
{
    protected static ?string $model = ClientVisitCupping::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListCuppings::route('/'),
            'create' => Pages\CreateCupping::route('/visits/{visit?}/create'),
            'edit' => Pages\EditCupping::route('/{record}/edit'),
            'cupping-point' => Pages\CuppingPointVisit::route('/{record}/cupping-point'),
        ];
    }
}
