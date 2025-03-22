<?php

namespace App\Filament\Exports;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Models\Discount;
use App\Models\Transaction;
use App\Models\TransactionDiscount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;
use Log;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoice_id')
                ->label('Invoice'),
            ExportColumn::make('amount')
                ->label('Amount'),
            ExportColumn::make('_payment_method')
                ->label('Payment method')
                ->state(function (Transaction $record) {
                    return match ($record->payment_method) {
                        PaymentMethod::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                        PaymentMethod::CASH => 'Cash',
                        PaymentMethod::QRIS => 'Qris',
                        default => '-',
                    };
                }),
            ExportColumn::make('_status')
                ->label('Status')
                ->state(function (Transaction $record) {
                    return match ($record->status) {
                        TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                        TransactionStatus::PAID => 'Lunas',
                        TransactionStatus::CANCEL => 'Dibatalkan',
                        default => '-',
                    };
                }),
            ExportColumn::make('items_1')
                ->label('Therapy Service Name')
                ->state(function (Transaction $record) {
                    $notAdditionalTransactions = array_filter($record->items->toArray(), function ($transaction) {
                        return $transaction['is_additional'] == 0;
                    });
                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                    if (count($notAdditionalTransactions) > 0) {
                        return $notAdditionalTransactions[0]['name'];
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('items_2')
                ->label('Therapy Service Price')
                ->state(function (Transaction $record) {
                    $notAdditionalTransactions = array_filter($record->items->toArray(), function ($transaction) {
                        return $transaction['is_additional'] == 0;
                    });
                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                    if (count($notAdditionalTransactions) > 0) {
                        return $notAdditionalTransactions[0]['price'];
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('items_3')
                ->label('Therapy Service Add')
                ->state(function (Transaction $record) {
                    $notAdditionalTransactions = array_filter($record->items->toArray(), function ($transaction) {
                        return $transaction['is_additional'] == 1;
                    });
                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                    if (count($notAdditionalTransactions) > 0) {
                        return $notAdditionalTransactions[0]['name'];
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('items_3')
                ->label('Therapy Service Add Price')
                ->state(function (Transaction $record) {
                    $notAdditionalTransactions = array_filter($record->items->toArray(), function ($transaction) {
                        return $transaction['is_additional'] == 1;
                    });
                    $notAdditionalTransactions = array_values($notAdditionalTransactions);
                    if (count($notAdditionalTransactions) > 0) {
                        return $notAdditionalTransactions[0]['price'];
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('discount_1')
                ->label('Discount')
                ->state(function (Transaction $state) {
                    if ($state->discount != null) {
                        return $state->discount->discount;
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('discount_2')
                ->label('Discount Name')
                ->state(function (Transaction $state) {
                    if ($state->discount != null) {
                        return $state->discount->name;
                    } else {
                        return "-";
                    }
                }),
            ExportColumn::make('clientVisit.client.reg_id')
                ->label('Client Reg Id'),
            ExportColumn::make('clientVisit.client.name')
                ->label('Client Name'),
            ExportColumn::make('clientVisit.therapy.name')
                ->label('Therapy Name'),
            ExportColumn::make('clientVisit.createdBy.name')
                ->label('Admin Name'),
            ExportColumn::make('created_at')
                ->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
