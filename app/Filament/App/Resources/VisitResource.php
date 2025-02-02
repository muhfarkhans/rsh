<?php

namespace App\Filament\App\Resources;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\VisitResource\Pages;
use App\Filament\App\Resources\VisitResource\RelationManagers;
use App\Helpers\FilamentHelper;
use App\Models\Client;
use App\Models\ClientVisit;
use Auth;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitResource extends Resource
{
    protected static ?string $model = ClientVisit::class;

    protected static ?string $navigationIcon = 'heroicon-c-users';

    protected static ?string $label = 'Visit';

    protected static ?int $navigationSort = 1;

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
                TextColumn::make('client.reg_id')
                    ->label('Registrasi Id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Petugas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            VisitStatus::WAITING_FOR_SERVICE => 'warning',
                            VisitStatus::ON_SERVICE => 'success',
                            VisitStatus::WAITING_FOR_PAYMENT => 'success',
                            VisitStatus::DONE => 'info',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            VisitStatus::WAITING_FOR_SERVICE => 'Menunggu layanan',
                            VisitStatus::ON_SERVICE => 'Dilakukan pelayanan',
                            VisitStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                            VisitStatus::DONE => 'Selesai',
                            default => '-',
                        };
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal kunjungan')
                    ->formatStateUsing(function (string $state): string {
                        $diff = Carbon::parse($state)->diffForHumans();
                        return __("{$state} ({$diff})");
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (in_array(Role::THERAPIST, Auth::user()->getRoleNames()->toArray())) {
                    $query->where('therapy_id', Auth::user()->id);
                }
            });
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
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
            'edit-service' => Pages\EditServiceVisit::route('/{record}/edit-service'),
            'view' => Pages\ViewVisit::route('/{record}'),
        ];
    }
}
