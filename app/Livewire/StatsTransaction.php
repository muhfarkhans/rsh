<?php

namespace App\Livewire;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class StatsTransaction extends BaseWidget
{
    public Transaction $transaction;

    public function getAmountByMethod($method, $isToday = false)
    {
        return Transaction::where('payment_method', $method)
            ->where('status', TransactionStatus::PAID)
            ->when($isToday, function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->sum('amount');
    }

    public function getAmount($isToday = false)
    {
        return Transaction::where('status', TransactionStatus::PAID)
            ->when($isToday, function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->sum('amount');
    }


    public function getSumAmount()
    {
        return [
            "all" => [
                "total" => $this->getAmount(),
                "method" => [
                    "cash" => $this->getAmountByMethod(PaymentMethod::CASH),
                    "qris" => $this->getAmountByMethod(PaymentMethod::QRIS),
                ]
            ],
            "today" => [
                "total" => $this->getAmount(true),
                "method" => [
                    "cash" => $this->getAmountByMethod(PaymentMethod::CASH, true),
                    "qris" => $this->getAmountByMethod(PaymentMethod::QRIS, true),
                ]
            ],
        ];
    }

    public function getTotalByMethod($method, $isToday = false)
    {
        return Transaction::where('payment_method', $method)
            ->where('status', TransactionStatus::PAID)
            ->when($isToday, function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->count();
    }

    public function getTotal($isToday = false)
    {
        return Transaction::where('status', TransactionStatus::PAID)
            ->when($isToday, function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->count();
    }

    public function getSumTotal()
    {
        return [
            "all" => [
                "total" => $this->getTotal(),
                "method" => [
                    "cash" => $this->getTotalByMethod(PaymentMethod::CASH),
                    "qris" => $this->getTotalByMethod(PaymentMethod::QRIS),
                ]
            ],
            "today" => [
                "total" => $this->getTotal(true),
                "method" => [
                    "cash" => $this->getTotalByMethod(PaymentMethod::CASH, true),
                    "qris" => $this->getTotalByMethod(PaymentMethod::QRIS, true),
                ]
            ],
        ];
    }


    protected function getStats(): array
    {
        $amount = $this->getSumAmount();
        $total = $this->getSumTotal();

        return [
            Stat::make(
                'Transaksi hari ini',
                $total['today']['total']
            )->description(
                    new HtmlString("
                <table style=\"width: 100%\">
                    <tr>
                        <td>Cash</td>
                        <td>: </td>
                        <td><strong>" . $total['today']['method']['cash'] . "</strong></td>
                    </tr>
                    <tr>
                        <td>Qris</td>
                        <td>: </td>
                        <td><strong>" . $total['today']['method']['qris'] . "</strong></td>
                    </tr>
                </table>
            ")
                ),
            Stat::make(
                'Uang masuk hari ini',
                number_format($amount['today']['total'], 0, ',', '.')
            )->description(
                    new HtmlString("
                <table style=\"width: 100%\">
                    <tr>
                        <td>Cash</td>
                        <td>: </td>
                        <td><strong>" . number_format($amount['today']['method']['cash'], 0, ',', '.') . "</strong></td>
                    </tr>
                    <tr>
                        <td>Qris</td>
                        <td>: </td>
                        <td><strong>" . number_format($amount['today']['method']['qris'], 0, ',', '.') . "</strong></td>
                    </tr>
                </table>
            ")
                ),
            Stat::make(
                'Total transaksi dilakukan',
                $total['all']['total']
            )->description(
                    new HtmlString("
                <table style=\"width: 100%\">
                    <tr>
                        <td>Cash</td>
                        <td>: </td>
                        <td><strong>" . $total['all']['method']['cash'] . "</strong></td>
                    </tr>
                    <tr>
                        <td>Qris</td>
                        <td>: </td>
                        <td><strong>" . $total['all']['method']['qris'] . "</strong></td>
                    </tr>
                </table>
            ")
                ),
            Stat::make(
                'Total uang masuk',
                number_format($amount['all']['total'], 0, ',', '.')
            )->description(
                    new HtmlString("
                <table style=\"width: 100%\">
                    <tr>
                        <td>Cash</td>
                        <td>: </td>
                        <td><strong>" . number_format($amount['all']['method']['cash'], 0, ',', '.') . "</strong></td>
                    </tr>
                    <tr>
                        <td>Qris</td>
                        <td>: </td>
                        <td><strong>" . number_format($amount['all']['method']['qris'], 0, ',', '.') . "</strong></td>
                    </tr>
                </table>
            ")
                ),
        ];
    }
}
