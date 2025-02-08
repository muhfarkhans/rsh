<?php

namespace App\Filament\App\Resources\VisitResource\Pages;

use App\Constants\Role;
use App\Constants\VisitStatus;
use App\Filament\App\Resources\VisitResource;
use Auth;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

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

    public function getTabs(): array
    {
        $dataStatus = [
            VisitStatus::REGISTER => 'Pendaftaran',
            VisitStatus::WAITING_FOR_CHECK => 'Menunggu Check Up',
            VisitStatus::WAITING_FOR_SERVICE => 'Menunggu layanan',
            VisitStatus::ON_SERVICE => 'Dilakukan pelayanan',
            VisitStatus::WAITING_FOR_PAYMENT => 'Menunggu pembayaran',
            VisitStatus::DONE => 'Selesai',
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
