<?php

namespace App\Filament\Resources\TransactionResource\Pages;

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
                                        TextEntry::make('therapy.name')
                                            ->label('Terapis')
                                            ->columnSpan(1),
                                        TextEntry::make('price')
                                            ->label('Harga')
                                            ->columnSpan(1),
                                        RepeatableEntry::make('additional')
                                            ->label('Tambahan')
                                            ->schema([
                                                TextEntry::make('service_name')
                                                    ->label('Nama')
                                                    ->columnSpanFull(),
                                                TextEntry::make('service_qty')
                                                    ->label('Qty')
                                                    ->columnSpan(1),
                                                TextEntry::make('service_price')
                                                    ->label('Harga')
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                        ])->columnSpan(2),
                    Section::make('Pembayaran')->schema([
                        TextEntry::make('total_services')
                            ->label('Service')
                            ->inlineLabel()
                            ->extraAttributes(['style' => 'text-align: right;'])
                            // ->content(function ($state) {
                            //     return new HtmlString(
                            //         '
                            //         <h1>' . number_format($state, 0, ',', '.') . '</h1>
                            //         '
                            //     );
                            // })
                            ->columnSpanFull(),
                        TextEntry::make('total_additional')
                            ->label('Tambahan')
                            ->inlineLabel()
                            ->extraAttributes(['style' => 'text-align: right;'])
                            // ->content(function ($state) {
                            //     return new HtmlString(
                            //         '
                            //         <h1>' . number_format($state, 0, ',', '.') . '</h1>
                            //         '
                            //     );
                            // })
                            ->columnSpanFull(),
                        TextEntry::make('total')
                            ->label('Total')
                            ->inlineLabel()
                            ->extraAttributes(['style' => 'text-align:right;font-size:1.25em;font-weight:bold;'])
                            // ->content(function ($state) {
                            //     return new HtmlString(
                            //         '
                            //         <h1>Rp. ' . number_format($state, 0, ',', '.') . '</h1>
                            //         '
                            //     );
                            // })
                            ->columnSpanFull(),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->columnSpanFull(),
                        TextEntry::make('updated_at')
                            ->hiddenLabel()
                            ->hint(function ($state) {
                                return new HtmlString(
                                    '
                                    <div style="text-align: right;">
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
