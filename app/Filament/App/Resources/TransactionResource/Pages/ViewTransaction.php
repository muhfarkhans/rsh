<?php

namespace App\Filament\App\Resources\TransactionResource\Pages;

use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->columns(3)->schema([
                    Section::make('Informasi pembayaran')
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
                        TextEntry::make('createdBy.name')
                            ->label('Cashier Name')
                            ->columnSpanFull(),
                        TextEntry::make('updated_at')
                            ->hiddenLabel()
                            ->hint(function ($state) {
                                return new HtmlString(
                                    '
                                    <div style="">
                                    <p>Update terakhir </p>
                                    ' . $state . '
                                    <p>*tombol save digunakan ketika layanan sudah diberikan</p>
                                    </div>'
                                );
                            }),
                    ])->columnSpan(1),
                ])
            ]);
    }
}
