<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTabs(): array
    {
        $dataStatus = [
            TransactionStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
            TransactionStatus::PAID => 'Lunas',
            TransactionStatus::CANCEL => 'Dibatalkan',
        ];

        $tabs = [
            "Semua" => Tab::make(),
        ];

        foreach ($dataStatus as $key => $status) {
            $tabs[$status] = Tab::make()->modifyQueryUsing(fn(Builder $query) => $query->where('status', $key));
        }

        return $tabs;
    }
}
