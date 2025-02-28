<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Constants\Role;
use App\Filament\Resources\UserResource;
use DB;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $idSuperAdmin = DB::table('roles')->where('name', Role::SUPER_ADMIN)->first()->id;
        $idTherapist = DB::table('roles')->where('name', Role::THERAPIST)->first()->id;
        $idCashier = DB::table('roles')->where('name', Role::CASHIER)->first()->id;
        $dataStatus = [
            $idSuperAdmin => 'Super Admin',
            $idCashier => 'Cashier',
            $idTherapist => 'Therapist',
        ];

        $tabs = [
            "Semua" => Tab::make(),
        ];

        foreach ($dataStatus as $key => $status) {
            $tabs[$status] = Tab::make()->modifyQueryUsing(function (Builder $query) use ($key) {
                $query->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.role_id', $key);
            });
        }

        return $tabs;
    }
}
