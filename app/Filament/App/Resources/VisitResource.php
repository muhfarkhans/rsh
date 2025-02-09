<?php

namespace App\Filament\App\Resources;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\VisitResource\Pages;
use App\Filament\App\Resources\VisitResource\RelationManagers;
use App\Helpers\FilamentHelper;
use App\Models\Client;
use App\Models\ClientVisit;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
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
                TextColumn::make('therapy.name')
                    ->label('Terapis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return match ($record->status) {
                            VisitStatus::REGISTER => 'warning',
                            VisitStatus::WAITING_FOR_CHECK => 'warning',
                            VisitStatus::WAITING_FOR_SERVICE => 'warning',
                            VisitStatus::ON_SERVICE => 'success',
                            VisitStatus::WAITING_FOR_PAYMENT => 'success',
                            VisitStatus::DONE => 'info',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            VisitStatus::REGISTER => 'Pendaftaran',
                            VisitStatus::WAITING_FOR_CHECK => 'Menunggu Check Up',
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
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    })
                    ->columnSpan(2)->columns(2),
                SelectFilter::make('therapy_id')
                    ->label('Terapis')
                    ->options(function () {
                        return User::with(['roles'])->whereHas('roles', function ($query) {
                            return $query->where('name', Role::THERAPIST);
                        })->get()->pluck('name', 'id');
                    })
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
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
