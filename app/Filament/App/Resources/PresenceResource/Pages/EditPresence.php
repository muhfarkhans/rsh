<?php

namespace App\Filament\App\Resources\PresenceResource\Pages;

use App\Filament\App\Resources\PresenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPresence extends EditRecord
{
    protected static string $resource = PresenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
