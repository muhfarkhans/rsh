<?php

namespace App\Livewire\Reports;

use App\Constants\Role;
use App\Constants\TransactionStatus;
use App\Filament\Exports\PayrollExporter;
use App\Filament\Resources\TransactionResource;
use App\Helpers\Helper;
use App\Models\ClientVisit;
use App\Models\Presence;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Pages\Actions\ButtonAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
// use Filament\Tables\Actions\ExportAction;

use Illuminate\Support\Facades\URL;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

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
        $now = Carbon::now();
        $startDate = $now->startOfMonth()->format('Y-m-d');
        $endDate = $now->endOfMonth()->format('Y-m-d');

        $createdFrom = request()->query('created_from', $startDate);
        $createdUntil = request()->query('created_until', $endDate);

        return $table
            ->query(
                User::query()->select('users.id', 'users.name')
                    // ->selectRaw('sum(services.commision) AS commision')
                    // ->selectRaw('count(client_visits.id) AS total_service')
                    ->selectRaw('count(users.email) AS total_service')
                    ->addSelect([
                        'commision' => ClientVisit::query()
                            // ->selectRaw('sum(client_visits.id)')
                            ->selectRaw('sum(services.commision)')
                            ->whereColumn('client_visits.therapy_id', 'users.id')
                            ->leftJoin('transactions', function ($join) {
                                $join->on('transactions.client_visit_id', '=', 'client_visits.id')
                                    ->where('transactions.status', 'paid');
                            })
                            ->leftJoin('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
                            ->leftJoin('services', 'services.id', '=', 'client_visit_cuppings.service_id')
                            ->whereDate('client_visits.created_at', '>=', $createdFrom)
                            ->whereDate('client_visits.created_at', '<=', $createdUntil)
                    ])
                    // ->selectRaw('count(presences.id) AS total_presence')
                    ->addSelect([
                        'total_presence' => Presence::query()
                            ->selectRaw('count(presences.id)')
                            ->whereColumn('presences.user_id', 'users.id')
                            ->whereDate('presences.created_at', '>=', $createdFrom)
                            ->whereDate('presences.created_at', '<=', $createdUntil)
                    ])
                    ->addSelect([
                        'attendance_allowance' => Presence::query()
                            ->selectRaw('count(presences.id) * 100000')
                            ->whereColumn('presences.user_id', 'users.id')
                            ->whereDate('presences.created_at', '>=', $createdFrom)
                            ->whereDate('presences.created_at', '<=', $createdUntil)
                    ])
                    ->addSelect([
                        'meal_allowance' => Presence::query()
                            ->selectRaw('count(presences.id) * 100000')
                            ->whereColumn('presences.user_id', 'users.id')
                            ->whereDate('presences.created_at', '>=', $createdFrom)
                            ->whereDate('presences.created_at', '<=', $createdUntil)
                    ])
                    ->addSelect([
                        'total_allowance' => Presence::query()
                            ->selectRaw('(count(presences.id) * 200000)')
                            ->whereColumn('presences.user_id', 'users.id')
                            ->whereDate('presences.created_at', '>=', $createdFrom)
                            ->whereDate('presences.created_at', '<=', $createdUntil)
                    ])
                    ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->whereIn('model_has_roles.role_id', [2, 3])
                    ->groupBy('users.id', 'users.name')
            )
            ->columns([
                // TextColumn::make('index')
                //     ->label('No.')
                //     ->rowIndex(),
                TextColumn::make('name')
                    ->label('Name')
                    // ->searchable('users.name')
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
                    ->sortable(),
                TextColumn::make('commision')
                    ->label('Commision')
                    ->formatStateUsing(fn(string $state): string => __(Helper::rupiah($state)))
                    ->sortable(),
                TextColumn::make('total_presence')
                    ->label('Total Present')
                    ->sortable(),
                TextColumn::make('attendance_allowance')
                    ->label('Attendance Allowance')
                    ->formatStateUsing(function ($record) {
                        return $record->total_presence * 100000;
                    }),
                TextColumn::make('meal_allowance')
                    ->label('Meal Allowance')
                    ->formatStateUsing(function ($record) {
                        return $record->total_presence * 100000;
                    }),
                TextColumn::make('total_allowance')
                    ->label('Total Allowance')
                    ->formatStateUsing(function ($record) {
                        return ($record->total_presence * 200000) + $record->commision;
                    })
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->default(Request::query('created_from', Carbon::now()->startOfMonth()->format('Y-m-d')))
                            ->afterStateUpdated(function (Get $get) {
                                $parsedUrl = parse_url(URL::previous());

                                $now = Carbon::now();
                                $startDate = $now->startOfMonth()->format('Y-m-d');
                                $endDate = $now->endOfMonth()->format('Y-m-d');

                                $queryParams = [
                                    'created_from' => $startDate,
                                    'created_until' => $endDate,
                                    'search' => '',
                                ];

                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }

                                $queryCreatedFrom = $get('created_from');
                                $queryCreatedUntil = $get('created_until');

                                return redirect()->route('filament.admin.pages.payroll-report', [
                                    'created_from' => $queryCreatedFrom,
                                    'created_until' => $queryCreatedUntil,
                                    'search' => $queryParams['search'],
                                ]);
                            }),
                        DatePicker::make('created_until')
                            ->default(Request::query('created_until', Carbon::now()->endOfMonth()->format('Y-m-d')))
                            ->afterStateUpdated(function (Get $get) {
                                $parsedUrl = parse_url(URL::previous());

                                $now = Carbon::now();
                                $startDate = $now->startOfMonth()->format('Y-m-d');
                                $endDate = $now->endOfMonth()->format('Y-m-d');

                                $queryParams = [
                                    'created_from' => $startDate,
                                    'created_until' => $endDate,
                                    'search' => '',
                                ];

                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }

                                $queryCreatedFrom = $get('created_from');
                                $queryCreatedUntil = $get('created_until');

                                return redirect()->route('filament.admin.pages.payroll-report', [
                                    'created_from' => $queryCreatedFrom,
                                    'created_until' => $queryCreatedUntil,
                                    'search' => $queryParams['search'],
                                ]);
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query;
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
                Filter::make('users.name')
                    ->form([
                        TextInput::make('users_name')
                            ->label('Name')
                            ->default(Request::query('search', ''))
                            ->afterStateUpdated(function (Get $get) {
                                $parsedUrl = parse_url(URL::previous());

                                $now = Carbon::now();
                                $startDate = $now->startOfMonth()->format('Y-m-d');
                                $endDate = $now->endOfMonth()->format('Y-m-d');

                                $queryParams = [
                                    'created_from' => $startDate,
                                    'created_until' => $endDate,
                                    'search' => '',
                                ];

                                if (isset($parsedUrl['query'])) {
                                    parse_str($parsedUrl['query'], $queryParams);
                                }

                                return redirect()->route('filament.admin.pages.payroll-report', [
                                    'created_from' => $queryParams['created_from'],
                                    'created_until' => $queryParams['created_until'],
                                    'search' => $get('users_name')
                                ]);
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('users.name', 'like', '%' . $data['users_name'] . '%');
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['users_name'] ?? null) {
                            $indicators[] = Indicator::make('Search ' . $data['users_name']);
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
                \Filament\Tables\Actions\Action::make('Ekspor user')
                    ->action(function () {
                        $parsedUrl = parse_url(URL::previous());

                        $now = Carbon::now();
                        $startDate = $now->startOfMonth()->format('Y-m-d');
                        $endDate = $now->endOfMonth()->format('Y-m-d');

                        $queryParams = [
                            'created_from' => $startDate,
                            'created_until' => $endDate,
                            'search' => '',
                        ];

                        if (isset($parsedUrl['query'])) {
                            parse_str($parsedUrl['query'], $queryParams);
                        }

                        return redirect()->route('excel.payroll', [
                            'created_from' => $queryParams['created_from'],
                            'created_until' => $queryParams['created_until'],
                            'search' => $queryParams['search'],
                        ]);
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.reports.list-transaction');
    }
}
