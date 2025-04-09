<?php

namespace App\Livewire\Reports;

use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Filament\Exports\PayrollExporter;
use App\Filament\Resources\TransactionResource;
use App\Helpers\Helper;
use App\Models\Presence;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Pages\Actions\ButtonAction;
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
use Illuminate\Support\Facades\Request;

class ListPayroll extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public static function table(Table $table): Table
    {
        // dd(url());
        // dd(User::query()
        //     ->select('users.id', 'users.name', 'presences.user_id')
        //     ->selectRaw('sum(services.commision) AS commision')
        //     // ->selectRaw('count(presences.id) AS total_presence')
        //     ->selectRaw('count(presences.id) AS total_presence')
        //     ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
        //     ->leftJoin('client_visits', 'client_visits.therapy_id', '=', 'users.id')
        //     ->leftJoin('transactions', function ($join) {
        //         $join->on('transactions.client_visit_id', '=', 'client_visits.id')
        //             ->where('transactions.status', 'paid');
        //     })
        //     ->leftJoin('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
        //     ->leftJoin('services', 'services.id', '=', 'client_visit_cuppings.service_id')
        //     // ->leftJoin('presences', 'users.id', '=', 'presences.user_id')
        //     ->whereIn('model_has_roles.role_id', [2, 3])
        //     ->groupBy('users.id', 'users.name', 'presences.id')
        //     ->toSql());

        // dd(url());

        $createdFrom = request()->query('created_from', 'default_value');
        $createdUntil = request()->query('created_until', 'default_value');

        // dd([$createdFrom, $createdUntil]);

        return $table
            ->query(
                User::query()->select('users.id', 'users.name')
                    ->selectRaw('sum(services.commision) AS commision')
                    ->selectRaw('count(client_visits.id) AS total_service')
                    ->selectRaw('count(presences.id) AS total_presence')
                    // ->addSelect([
                    //     'total_presence' => Presence::query()
                    //         ->selectRaw('count(presences.id)')
                    //         ->whereColumn('presences.user_id', 'users.id')
                    //         ->whereDate('presences.created_at', '>=', $createdFrom)
                    //         ->whereDate('presences.created_at', '<=', $createdUntil)
                    // ])
                    ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->leftJoin('client_visits', 'client_visits.therapy_id', '=', 'users.id')
                    ->leftJoin('transactions', function ($join) {
                        $join->on('transactions.client_visit_id', '=', 'client_visits.id')
                            ->where('transactions.status', 'paid');
                    })
                    // ->leftJoin('transactions', 'transactions.client_visit_id', '=', 'client_visits.id')
                    ->leftJoin('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
                    ->leftJoin('services', 'services.id', '=', 'client_visit_cuppings.service_id')
                    ->leftJoin('presences', 'users.id', '=', 'presences.user_id')
                    ->whereIn('model_has_roles.role_id', [2, 3])
                    // ->whereDate('client_visits.created_at', '>=', $createdFrom)
                    // ->whereDate('client_visits.created_at', '<=', $createdUntil)
                    // ->where('transactions.status', 'paid')
                    ->groupBy('users.id', 'users.name')
            )
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable('users.name')
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Role::SUPER_ADMIN => 'success',
                        Role::CASHIER => 'info',
                        Role::THERAPIST => 'warning',
                    })
                    ->formatStateUsing(fn($state) => str($state)->headline())
                    ->searchable('roles.name')
                    ->sortable(),
                TextColumn::make('commision')
                    ->label('Commision')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->sortable(),
                TextColumn::make('total_presence')
                    ->label('Total Present')
                    ->formatStateUsing(function ($record) {
                        // if ($record->total_service != 0) {
                        //     return $record->total_presence / $record->total_service;
                        // }
            
                        return $record->total_presence;
                    })
                    ->sortable(),
            ])
            // ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                        // DatePicker::make('created_from')->default(Request::query('created_from', '2025-04-01')),
                        // DatePicker::make('created_until')->default(Request::query('created_until', '2025-04-30')),
                        // Actions::make([
                        //     Action::make('Generate Payroll')
                        //         ->action(function (Get $get) {
                        //             $queryCreatedFrom = $get('created_from');
                        //             $queryCreatedUntil = $get('created_until');

                        //             return redirect()->route('filament.admin.pages.payroll-report', [
                        //                 'created_from' => $queryCreatedFrom,
                        //                 'created_until' => $queryCreatedUntil
                        //             ]);
                        //         })
                        // ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // return $query;
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('client_visits.created_at', '>=', $date)
                                    ->whereDate('presences.created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('client_visits.created_at', '<=', $date)
                                    ->whereDate('presences.created_at', '<=', $date),
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
                    ->exporter(PayrollExporter::class)
                // ->modifyQueryUsing(function (Builder $query, array $options) {
                //     $createdFrom = request()->query('created_from', 'default_value');
                //     $createdUntil = request()->query('created_until', 'default_value');

                //     return $query->whereDate('client_visits.created_at', '>=', $createdFrom)
                //         ->whereDate('client_visits.created_at', '<=', $createdUntil);
                // })
            ]);
    }

    public function render()
    {
        return view('livewire.reports.list-transaction');
    }
}
