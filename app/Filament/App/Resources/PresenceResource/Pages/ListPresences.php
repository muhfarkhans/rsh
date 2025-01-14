<?php

namespace App\Filament\App\Resources\PresenceResource\Pages;

use App\Filament\App\Resources\PresenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPresences extends ListRecords
{
    protected static string $resource = PresenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
