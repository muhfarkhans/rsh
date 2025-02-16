<?php

namespace App\Livewire\Reports;

use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListTransaction extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query())
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
                TextColumn::make('clientVisit.therapy.name')
                    ->label('Terapis')
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
                TextColumn::make('created_at')
                    ->label('Created at')
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
                SelectFilter::make('clientVisit.therapy_id')
                    ->label('Terapis')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value']))
                            $query->whereHas(
                                'clientVisit',
                                fn(Builder $query) => $query->where('therapy_id', '=', (int) $data['value'])
                            );
                    })
                    ->options(function () {
                        return User::with(['roles'])->whereHas('roles', function ($query) {
                            return $query->where('name', Role::THERAPIST);
                        })->get()->pluck('name', 'id');
                    }),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(function () {
                        return [
                            TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu Pembayaran',
                            TransactionStatus::PAID => 'Lunas',
                            TransactionStatus::CANCEL => 'Lunas',
                        ];
                    })
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
            ->actions([
                ViewAction::make()
                    ->url(fn(Transaction $record) => TransactionResource::getUrl('view', ['record' => $record->id]))
            ])
            ->headerActions([
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.reports.list-transaction');
    }
}
