<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role;
use App\Filament\App\Resources\VisitResource;
use Auth;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        if (!in_array(Role::THERAPIST, Auth::user()->getRoleNames()->toArray())) {
            $actions = [Actions\CreateAction::make()];
        }

        return $actions;
    }
}
