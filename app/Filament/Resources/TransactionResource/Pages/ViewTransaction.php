<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\Actions\Action;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->columns(3)->schema([
                    Section::make('Informasi pembayaran')
                        ->headerActions([
                            Action::make('edit')
                                ->label('Bayar')
                                ->icon('heroicon-o-pencil')
                                ->url(function (Transaction $record) {
                                    return TransactionResource::getUrl('edit', ['record' => $record]);
                                })
                                ->visible(function () {
                                    if (in_array($this->record->status, [TransactionStatus::WAITING_FOR_PAYMENT])) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }),
                            Action::make('download_struck')
                                ->label(function () {
                                    return "Download Struk";
                                })
                                ->visible(function () {
                                    if (in_array($this->record->status, [TransactionStatus::PAID])) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                })
                                ->action(function () {
                                    $nameAdditionalService = "-";
                                    $totalPriceAdditionalService = 0;
                                    $nameService = "-";
                                    $totalPriceService = 0;
                                    foreach ($this->record->items as $key => $item) {
                                        if ($item->is_additional == 0) {
                                            $totalPriceService += $item->price;
                                            $nameService = $item->name;
                                        } else {
                                            $totalPriceAdditionalService += $item->price;
                                            $nameAdditionalService = $item->name;
                                        }
                                    }

                                    $data = [
                                        'invoice_id' => $this->record->invoice_id,
                                        'client_name' => $this->record->clientVisit->client->name,
                                        'cashier_name' => $this->record->createdBy->name,
                                        'created_at' => $this->record->updated_at,
                                        'service_name' => $nameService,
                                        'amount_service' => $totalPriceService,
                                        'amount_add_name' => $nameAdditionalService,
                                        'amount_add' => $totalPriceAdditionalService,
                                        'discount_name' => "",
                                        'discount_price' => 0,
                                        'total' => $this->record->amount,
                                        'payment_method' => $this->record->payment_method,
                                    ];

                                    if ($this->record->discount != null) {
                                        $data['discount_name'] = $this->record->discount->name;
                                        $data['discount_price'] = $this->record->discount->discount;
                                    }

                                    $pdf = Pdf::loadView('pdf.struct', ['data' => $data]);
                                    return response()->streamDownload(function () use ($pdf) {
                                        echo $pdf->stream();
                                    }, 'Struct-' . $this->record->invoice_id . '.pdf');
                                })
                        ])
                        ->description('Informasi client dan layanan')
                        ->schema([
                            Grid::make()->columns(2)->schema([
                                TextEntry::make('invoice_id')
                                    ->label('No Invoice')
                                    ->columnSpanFull(),
                                TextEntry::make('clientVisit.client.name')
                                    ->label('Nama')
                                    ->columnSpanFull(),
                                TextEntry::make('clientVisit.client.phone')
                                    ->label('No Telepon'),
                                TextEntry::make('clientVisit.client.job')
                                    ->label('Pekerjaan'),
                                TextEntry::make('clientVisit.created_at')
                                    ->label('Tanggal visit')
                                    ->columnSpanFull(),
                                TextEntry::make('clientVisit.client.address')
                                    ->label('Alamat')
                                    ->columnSpanFull(),
                                RepeatableEntry::make('items')
                                    ->label('Service')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nama')
                                            ->columnSpanFull(),
                                        TextEntry::make('transaction.clientVisit.therapy.name')
                                            ->label('Terapis')
                                            ->columnSpan(1),
                                        TextEntry::make('price')
                                            ->label('Harga')
                                            ->columnSpan(1),
                                        TextEntry::make('qty')
                                            ->label('Qty')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                        ])->columnSpan(2),
                    Section::make('Pembayaran')->schema([
                        TextEntry::make('total_services')
                            ->label('Service')
                            ->extraAttributes(['style' => 'text-align: right;'])
                            ->state(function ($record) {
                                $totalPriceService = 0;
                                $totalPriceAdditionalService = 0;

                                foreach ($record->items as $key => $item) {
                                    if ($item->is_additional == 0) {
                                        $totalPriceService += $item->price;
                                    } else {
                                        $totalPriceAdditionalService += $item->price;
                                    }
                                }

                                return new HtmlString(
                                    '
                                    <h1>Rp. ' . number_format($totalPriceService, 0, ',', '.') . '</h1>
                                    '
                                );
                            })
                            ->columnSpanFull(),
                        TextEntry::make('total_additional')
                            ->label('Tambahan')
                            ->extraAttributes(['style' => 'text-align: right;'])
                            ->state(function ($record) {
                                $totalPriceService = 0;
                                $totalPriceAdditionalService = 0;

                                foreach ($record->items as $key => $item) {
                                    if ($item->is_additional == 0) {
                                        $totalPriceService += $item->price;
                                    } else {
                                        $totalPriceAdditionalService += $item->price;
                                    }
                                }

                                return new HtmlString(
                                    '
                                    <h1>Rp. ' . number_format($totalPriceAdditionalService, 0, ',', '.') . '</h1>
                                    '
                                );
                            })
                            ->columnSpanFull(),
                        TextEntry::make('discount')
                            ->label('Diskon')
                            ->extraAttributes(['style' => 'text-align: right;'])
                            ->state(function ($record) {
                                $discount = 0;
                                if ($record->discount != null) {
                                    $discount = $record->discount->discount;
                                }

                                return new HtmlString(
                                    '
                                    <h1 style="color: red">Rp. ' . number_format($discount, 0, ',', '.') . '</h1>
                                    '
                                );
                            })
                            ->columnSpanFull(),
                        TextEntry::make('total')
                            ->label('Total')
                            ->extraAttributes(['style' => 'text-align:right;font-size:1.25em;font-weight:bold;'])
                            ->state(function ($record) {
                                return new HtmlString(
                                    '
                                    <h1>Rp. ' . number_format($record->amount, 0, ',', '.') . '</h1>
                                    '
                                );
                            })
                            ->columnSpanFull(),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->badge()
                            ->color(function ($record) {
                                return match ($record->payment_method) {
                                    PaymentMethod::WAITING_FOR_PAYMENT => 'success',
                                    PaymentMethod::CASH => 'info',
                                    PaymentMethod::QRIS => 'info',
                                    default => 'secondary',
                                };
                            })
                            ->getStateUsing(function ($record) {
                                return match ($record->payment_method) {
                                    PaymentMethod::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
                                    PaymentMethod::CASH => 'Cash',
                                    PaymentMethod::QRIS => 'Qris',
                                    default => '-',
                                };
                            })
                            ->columnSpanFull(),
                        TextEntry::make('status')
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
                            ->columnSpanFull(),
                        ImageEntry::make('photo')
                            ->extraImgAttributes(['style' => 'width: 100%'])
                            ->columnSpanFull()
                            ->visible(function ($record) {
                                if ($record->payment_method == PaymentMethod::QRIS) {
                                    return true;
                                }

                                return false;
                            }),
                        TextEntry::make('createdBy.name')
                            ->label('Cashier Name')
                            ->columnSpanFull(),
                        TextEntry::make('updated_at')
                            ->label('Update terakhir')
                    ])->columnSpan(1)
                ])
            ]);
    }
}
