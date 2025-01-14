<?php

namespace App\Filament\Resources\MasterData\DiscountResource\Pages;

use App\Filament\Resources\MasterData\DiscountResource;
use App\Models\Discount;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->using(function (Discount $discount, array $data) {
                $data['created_by'] = Auth::user()->id;

                $discount->create($data);
            }),
        ];
    }
}
