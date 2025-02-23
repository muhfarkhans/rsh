<?php

namespace App\Livewire;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Models\ClientVisit;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ListClientVisit extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public int $clientId;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ClientVisit::query()->where('client_id', $this->clientId)
            )
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

    public function render()
    {
        return view('livewire.list-client-visit');
    }
}
