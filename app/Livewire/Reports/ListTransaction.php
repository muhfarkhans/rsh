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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class ListTransaction extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->leftJoin('transaction_items', function ($join) {
                        $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                            ->where('transaction_items.is_additional', '=', 1);
                    })
                    ->select('transactions.*', 'transaction_items.name as service_name')
            )
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
                ExportAction::make()->exports([
                    ExcelExport::make()->withColumns([
                        Column::make('invoice_id')
                            ->heading('Invoice'),
                        Column::make('amount')
                            ->heading('Amount'),
                        Column::make('payment_method')
                            ->heading('Payment Method'),
                        Column::make('status')
                            ->heading('Status')
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                                    TransactionStatus::PAID => 'Lunas',
                                    TransactionStatus::CANCEL => 'Dibatalkan',
                                    default => '-',
                                };
                            }),
                        Column::make('service_name')
                            ->heading('Service Name')
                            ->formatStateUsing(function ($state) {
                                dd($state);
                            }),
                        Column::make('itemServiceName')
                            ->heading('Therapy Service Name')
                            ->formatStateUsing(function ($state) {
                                $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                    return $transaction['is_additional'] == 0;
                                });
                                $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                if (count($notAdditionalTransactions) > 0) {
                                    return $notAdditionalTransactions[0]['name'];
                                } else {
                                    return "-";
                                }
                            }),
                        Column::make('itemServicePrice')
                            ->heading('Therapy Service Price')
                            ->formatStateUsing(function ($state) {
                                $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                    return $transaction['is_additional'] == 0;
                                });
                                $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                if (count($notAdditionalTransactions) > 0) {
                                    return $notAdditionalTransactions[0]['price'];
                                } else {
                                    return "-";
                                }
                            }),
                        Column::make('itemServiceAddName')
                            ->heading('Therapy Service Add')
                            ->formatStateUsing(function ($state) {
                                $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                    return $transaction['is_additional'] == 1;
                                });
                                $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                if (count($notAdditionalTransactions) > 0) {
                                    return $notAdditionalTransactions[0]['name'];
                                } else {
                                    return "-";
                                }
                            }),
                        Column::make('itemServiceAddPrice')
                            ->heading('Therapy Service Add Price')
                            ->formatStateUsing(function ($state) {
                                $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                    return $transaction['is_additional'] == 1;
                                });
                                $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                if (count($notAdditionalTransactions) > 0) {
                                    return $notAdditionalTransactions[0]['price'];
                                } else {
                                    return "-";
                                }
                            }),
                        Column::make('clientVisit.client.reg_id')
                            ->heading('Client Reg Id'),
                        Column::make('clientVisit.client.name')
                            ->heading('Client Name'),
                        Column::make('clientVisit.therapy.name')
                            ->heading('Therapy Name'),
                        Column::make('clientVisit.createdBy.name')
                            ->heading('Admin Name'),
                        Column::make('created_at')
                            ->heading('Created At'),
                    ])->withFilename(date('Y-m-d') . '-Transaksi'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->withColumns([
                            Column::make('invoice_id')
                                ->heading('Invoice'),
                            Column::make('amount')
                                ->heading('Amount'),
                            Column::make('payment_method')
                                ->heading('Payment Method'),
                            Column::make('status')
                                ->heading('Status')
                                ->formatStateUsing(function ($state) {
                                    return match ($state) {
                                        TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                                        TransactionStatus::PAID => 'Lunas',
                                        TransactionStatus::CANCEL => 'Dibatalkan',
                                        default => '-',
                                    };
                                }),
                            Column::make('service_name')
                                ->heading('Service Name')
                                ->formatStateUsing(function ($state) {
                                    dd($state);
                                }),
                            Column::make('itemServiceName')
                                ->heading('Therapy Service Name')
                                ->formatStateUsing(function ($state) {
                                    $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                        return $transaction['is_additional'] == 0;
                                    });
                                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                    if (count($notAdditionalTransactions) > 0) {
                                        return $notAdditionalTransactions[0]['name'];
                                    } else {
                                        return "-";
                                    }
                                }),
                            Column::make('itemServicePrice')
                                ->heading('Therapy Service Price')
                                ->formatStateUsing(function ($state) {
                                    $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                        return $transaction['is_additional'] == 0;
                                    });
                                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                    if (count($notAdditionalTransactions) > 0) {
                                        return $notAdditionalTransactions[0]['price'];
                                    } else {
                                        return "-";
                                    }
                                }),
                            Column::make('itemServiceAddName')
                                ->heading('Therapy Service Add')
                                ->formatStateUsing(function ($state) {
                                    $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                        return $transaction['is_additional'] == 1;
                                    });
                                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                    if (count($notAdditionalTransactions) > 0) {
                                        return $notAdditionalTransactions[0]['name'];
                                    } else {
                                        return "-";
                                    }
                                }),
                            Column::make('itemServiceAddPrice')
                                ->heading('Therapy Service Add Price')
                                ->formatStateUsing(function ($state) {
                                    $notAdditionalTransactions = array_filter($state->toArray(), function ($transaction) {
                                        return $transaction['is_additional'] == 1;
                                    });
                                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                                    if (count($notAdditionalTransactions) > 0) {
                                        return $notAdditionalTransactions[0]['price'];
                                    } else {
                                        return "-";
                                    }
                                }),
                            Column::make('clientVisit.client.reg_id')
                                ->heading('Client Reg Id'),
                            Column::make('clientVisit.client.name')
                                ->heading('Client Name'),
                            Column::make('clientVisit.therapy.name')
                                ->heading('Therapy Name'),
                            Column::make('clientVisit.createdBy.name')
                                ->heading('Admin Name'),
                            Column::make('created_at')
                                ->heading('Created At'),
                        ])->withFilename('Transaksi'),
                    ])
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.reports.list-transaction');
    }
}
