<?php

namespace App\Filament\Resources\MasterData\DiscountResource\Pages;

use App\Filament\Resources\MasterData\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;
}
