<?php

namespace App\Livewire\Reports;

use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Filament\Exports\CommisionExporter;
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
use Filament\Tables\Actions\ExportAction;
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

class ListCommision extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
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
            )
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Therapist')
                    ->searchable('users.name')
                    ->sortable(),
                TextColumn::make('commision')
                    ->label('Commision')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->sortable(),
                TextColumn::make('total_service')
                    ->label('Total Service')
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
                ExportAction::make()
                    ->exporter(CommisionExporter::class)
            ]);
    }

    public function render()
    {
        return view('livewire.reports.list-commision');
    }
}
