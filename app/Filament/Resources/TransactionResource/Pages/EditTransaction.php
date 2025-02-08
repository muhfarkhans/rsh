<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Constants\VisitStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditTransaction extends EditRecord
{
    protected Transaction $transaction;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getFormActions(): array
    {
        return [
            //
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $client = $this->record->clientVisit->client;
        $items = $this->record->items;
        $clientServices = [];
        $totalPriceService = 0;
        $totalPriceAdditionalService = 0;

        foreach ($items as $key => $item) {
            if ($item->is_additional == 0) {
                if (!isset($clientServices[$item->service_id])) {
                    $clientServices[$item->service_id] = [
                        'service_id' => $item->service_id,
                        'service_name' => $item->name,
                        'service_therapy' => $this->record->clientVisit->therapy->name,
                        'service_price' => $item->price,
                        'service_qty' => $item->qty,
                        'additional' => [],
                    ];
                    $totalPriceService += $item->price;
                } else if (count($clientServices[$item->service_id]) == 0) {
                    $clientServices[$item->service_id]['service_id'] = $item->service_id;
                    $clientServices[$item->service_id]['service_name'] = $item->name;
                    $clientServices[$item->service_id]['service_therapy'] = $this->record->clientVisit->therapy->name;
                    $clientServices[$item->service_id]['service_price'] = $item->price;
                    $clientServices[$item->service_id]['service_qty'] = $item->qty;
                    $totalPriceService += $item->price;
                }
            } else {
                if (!isset($clientServices[$item->service_id])) {
                    $clientServices[$item->service_id] = [];
                }

                $clientServices[$item->service_id]['additional'][] = [
                    'service_id' => $item->service_id,
                    'service_name' => $item->name,
                    'service_therapy' => $this->record->clientVisit->therapy->name,
                    'service_price' => $item->price,
                    'service_qty' => $item->qty,
                ];
                $totalPriceAdditionalService += $item->price;
            }
        }

        $data['client_name'] = $client->name;
        $data['client_phone'] = $client->phone;
        $data['client_job'] = $client->job;
        $data['client_address'] = $client->address;
        $data['client_visit_created_at'] = $this->record->clientVisit->created_at;
        $data['client_services'] = $clientServices;
        $data['total'] = $this->record->amount;
        $data['total_services'] = $totalPriceService;
        $data['total_additional'] = $totalPriceAdditionalService;

        if ($data['payment_method'] == PaymentMethod::WAITING_FOR_PAYMENT) {
            unset($data['payment_method']);
        }

        if ($data['status'] == PaymentMethod::WAITING_FOR_PAYMENT) {
            unset($data['status']);
        }

        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make()->columns(3)->schema([
                Section::make('Informasi pembayaran')
                    ->description('Informasi client dan layanan')
                    ->schema([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('invoice_id')
                                ->label('No Invoice')
                                ->readOnly()
                                ->columnSpanFull(),
                            TextInput::make('client_name')
                                ->label('Nama')
                                ->readOnly()
                                ->columnSpanFull(),
                            TextInput::make('client_phone')
                                ->numeric()
                                ->readOnly()
                                ->label('No Telepon'),
                            TextInput::make('client_job')
                                ->readOnly()
                                ->label('Pekerjaan'),
                            TextInput::make('client_visit_created_at')
                                ->label('Tanggal visit')
                                ->readOnly()
                                ->columnSpanFull(),
                            TextInput::make('client_address')
                                ->label('Alamat')
                                ->readOnly()
                                ->columnSpanFull(),
                            Repeater::make('client_services')
                                ->label('Service')
                                ->schema([
                                    TextInput::make('service_name')
                                        ->label('Nama')
                                        ->readOnly()
                                        ->columnSpanFull(),
                                    TextInput::make('service_therapy')
                                        ->label('Terapis')
                                        ->readOnly()
                                        ->columnSpan(1),
                                    TextInput::make('service_price')
                                        ->label('Harga')
                                        ->readOnly()
                                        ->columnSpan(1),
                                    Repeater::make('additional')
                                        ->label('Tambahan')
                                        ->schema([
                                            TextInput::make('service_name')
                                                ->label('Nama')
                                                ->readOnly()
                                                ->columnSpanFull(),
                                            TextInput::make('service_qty')
                                                ->label('Qty')
                                                ->readOnly()
                                                ->columnSpan(1),
                                            TextInput::make('service_price')
                                                ->label('Harga')
                                                ->readOnly()
                                                ->columnSpan(1),
                                        ])
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columns(2)
                                        ->columnSpanFull(),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->columns(2)
                                ->columnSpanFull(),
                        ])->columnSpanFull(),
                    ])->columnSpan(2),
                Section::make('Pembayaran')->schema([
                    Placeholder::make('total_services')
                        ->label('Service')
                        ->inlineLabel()
                        ->extraAttributes(['style' => 'text-align: right;'])
                        ->content(function ($state) {
                            return new HtmlString(
                                '
                                <h1>' . number_format($state, 0, ',', '.') . '</h1>
                                '
                            );
                        })->columnSpanFull(),
                    Placeholder::make('total_additional')
                        ->label('Tambahan')
                        ->inlineLabel()
                        ->extraAttributes(['style' => 'text-align: right;'])
                        ->content(function ($state) {
                            return new HtmlString(
                                '
                                <h1>' . number_format($state, 0, ',', '.') . '</h1>
                                '
                            );
                        })->columnSpanFull(),
                    Placeholder::make('total')
                        ->label('Total')
                        ->inlineLabel()
                        ->extraAttributes(['style' => 'text-align:right;font-size:1.25em;font-weight:bold;'])
                        ->content(function ($state) {
                            return new HtmlString(
                                '
                                <h1>Rp. ' . number_format($state, 0, ',', '.') . '</h1>
                                '
                            );
                        })->columnSpanFull(),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(PaymentMethod::getLabels())
                        ->required()
                        ->live()
                        ->columnSpanFull(),
                    FileUpload::make('photo')
                        ->directory('transactions')
                        ->required(condition: function (Get $get) {
                            $paymentMethod = $get('payment_method');

                            if ($paymentMethod == PaymentMethod::QRIS) {
                                return true;
                            }

                            return false;
                        })
                        ->hidden(condition: function (Get $get) {
                            $paymentMethod = $get('payment_method');

                            if ($paymentMethod == PaymentMethod::QRIS) {
                                return false;
                            }

                            return true;
                        }),
                    Select::make('status')
                        ->label('Status')
                        ->options(TransactionStatus::getLabels())
                        ->required()
                        ->columnSpanFull(),
                    Placeholder::make('updated_at')
                        ->hiddenLabel()
                        ->hint(function ($state) {
                            return new HtmlString(
                                '
                                <div style="text-align: right;">
                                <p>Update terakhir </p>
                                ' . $state . '
                                <p>*tombol save digunakan ketika layanan sudah diberikan</p>
                                </div>'
                            );
                        }),
                    \Filament\Forms\Components\Actions::make([
                        Action::make('Save')
                            ->action(function ($livewire) {
                                $livewire->save();
                                $this->record->refresh();
                            })
                            ->disabled(function () {
                                if ($this->record->clientVisit->status != VisitStatus::WAITING_FOR_PAYMENT) {
                                    return true;
                                } else {
                                    return false;
                                }
                            })
                    ])->fullWidth(),
                ])->columnSpan(1),
            ])
        ];
    }
}
