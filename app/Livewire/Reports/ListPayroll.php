<?php

namespace App\Livewire\Reports;

use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use App\Helpers\Helper;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DB;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class ListPayroll extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // Transaction::query()

                User::query()->select('users.id', 'users.name')
                    ->selectRaw('sum(services.commision) AS commision')
                    ->selectRaw('count(client_visits.id) AS total_service')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('client_visits', 'client_visits.therapy_id', '=', 'users.id')
                    ->join('transactions', 'transactions.client_visit_id', '=', 'client_visits.id')
                    ->join('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
                    ->join('services', 'services.id', '=', 'client_visit_cuppings.service_id')
                    ->where('model_has_roles.role_id', 2)
                    ->where('transactions.status', 'paid')
                    ->groupBy('users.id', 'users.name')

                // Transaction::select('client_visits.therapy_id', 'transactions.created_at')
                //     ->selectRaw('sum(transactions.amount) as total')
                //     ->join('client_visits', 'transactions.client_visit_id', '=', 'client_visits.id')
                //     ->where('transactions.status', TransactionStatus::PAID)
                //     ->query()

                // Transaction::query()->select('client_visits.therapy_id as id')
                //     ->selectRaw('transactions.amount as total')
                //     ->selectRaw('sum(transactions.amount) as total')
                //     ->join('client_visits', 'transactions.client_visit_id', '=', 'client_visits.id')
                //     ->where('transactions.status', TransactionStatus::PAID)
                //     ->groupBy('client_visits.therapy_id', 'transactions.id')

                // Transaction::query()
                //     ->leftJoin('transaction_items', function ($join) {
                //         $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                //             ->where('transaction_items.is_additional', '=', 1);
                //     })
                //     ->select('transactions.*', 'transaction_items.name as service_name')
            )
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Therapist')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commision')
                    ->label('Commision')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_service')
                    ->label('Total Service')
                    ->searchable()
                    ->sortable(),
            ])
            // ->defaultSort('created_at', 'desc')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('client_visits.created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('client_visits.created_at', '<=', $date),
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
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
            ->actions([
                // ViewAction::make()
                //     ->url(fn(Transaction $record) => TransactionResource::getUrl('view', ['record' => $record->id]))
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        Column::make('created_at')
                            ->heading('Created At'),
                    ])->withFilename(date('Y-m-d') . '-Payroll'),
                ]),
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
