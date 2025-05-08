<?php

namespace App\Exports;

use App\Models\ClientVisit;
use App\Models\Presence;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PayrollExport implements FromQuery, WithMapping, WithHeadings, WithColumnWidths
{
    use Exportable;

    public $createdFrom;

    public $createdUntil;

    public $search;

    public function __construct($createdFrom, $createdUntil, $search)
    {
        $this->createdFrom = $createdFrom;
        $this->createdUntil = $createdUntil;
        $this->search = $search;
    }

    public function map($record): array
    {
        return [
            $record->name,
            $record->commision,
            $record->total_presence,
            $record->total_presence * 100000,
            $record->total_presence * 100000,
            ($record->total_presence * 100000) + ($record->total_presence * 100000) + $record->commision,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Commision',
            'Total Presence',
            'Attendance allowance',
            'Meal allowance',
            'Total',
        ];
    }

    public function query()
    {
        return User::query()->select('users.id', 'users.name')
            // ->addSelect([
            //     'commision' => ClientVisit::query()
            //         ->selectRaw('sum(services.commision)')
            //         ->whereColumn('client_visits.therapy_id', 'users.id')
            //         ->leftJoin('transactions', function ($join) {
            //             $join->on('transactions.client_visit_id', '=', 'client_visits.id')
            //                 ->where('transactions.status', 'paid');
            //         })
            //         ->leftJoin('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
            //         ->leftJoin('services', 'services.id', '=', 'client_visit_cuppings.service_id')
            //         ->whereDate('client_visits.created_at', '>=', $this->createdFrom)
            //         ->whereDate('client_visits.created_at', '<=', $this->createdUntil)
            // ])
            ->addSelect([
                'commision' => ClientVisit::query()
                    ->selectRaw('sum(services.commision)')
                    ->whereColumn('client_visits.therapy_id', 'users.id')
                    ->leftJoin('transactions', function ($join) {
                        $join->on('transactions.client_visit_id', '=', 'client_visits.id')
                            ->where('transactions.status', 'paid');
                    })
                    ->leftJoin('transaction_items', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->whereDate('client_visits.created_at', '>=', $this->createdFrom)
                    ->whereDate('client_visits.created_at', '<=', $this->createdUntil)
            ])
            ->addSelect([
                'total_service' => ClientVisit::query()
                    ->selectRaw('count(services.id)')
                    ->whereColumn('client_visits.therapy_id', 'users.id')
                    ->leftJoin('transactions', function ($join) {
                        $join->on('transactions.client_visit_id', '=', 'client_visits.id')
                            ->where('transactions.status', 'paid');
                    })
                    ->leftJoin('client_visit_cuppings', 'client_visit_cuppings.client_visit_id', '=', 'client_visits.id')
                    ->leftJoin('services', 'services.id', '=', 'client_visit_cuppings.service_id')
                    ->whereDate('client_visits.created_at', '>=', $this->createdFrom)
                    ->whereDate('client_visits.created_at', '<=', $this->createdUntil)
            ])
            ->addSelect([
                'total_presence' => Presence::query()
                    ->selectRaw('count(presences.id)')
                    ->whereColumn('presences.user_id', 'users.id')
                    ->whereDate('presences.created_at', '>=', $this->createdFrom)
                    ->whereDate('presences.created_at', '<=', $this->createdUntil)
            ])
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->whereIn('model_has_roles.role_id', [2, 3])
            ->where('users.name', 'like', '%' . $this->search . '%')
            ->groupBy('users.id', 'users.name');
    }
}
