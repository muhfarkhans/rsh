<?php

namespace App\Filament\App\Resources\CuppingResource\Pages;

use App\Filament\App\Resources\CuppingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuppings extends ListRecords
{
    protected static string $resource = CuppingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
