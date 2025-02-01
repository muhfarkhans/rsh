<?php

namespace App\Filament\Resources;

use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('invoice_id')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('clientVisit.client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            TransactionStatus::WAITING_FOR_PAYMENT => 'success',
                            TransactionStatus::PAID => 'info',
                            TransactionStatus::CANCEL => 'danger',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                            TransactionStatus::PAID => 'Lunas',
                            TransactionStatus::CANCEL => 'Dibatalkan',
                            default => '-',
                        };
                    })
                    ->sortable(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
