<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Constants\VisitStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\ClientVisit;
use App\Models\Discount;
use App\Models\Transaction;
use App\Models\TransactionDiscount;
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
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
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
        $discount = $this->record->discount;
        $clientServices = [];
        $totalPriceService = 0;
        $totalPriceAdditionalService = 0;

        // dd($this->record, $this->record->discount);

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

        if ($discount != null) {
            $data['discount'] = $discount->discount;
            $data['total_discount'] = $this->record->total_discount;
            $data['discount_code_used'] = $discount->code;
            $data['total'] = $this->record->amount;
        }

        if ($data['payment_method'] == PaymentMethod::WAITING_FOR_PAYMENT) {
            unset($data['payment_method']);
        }

        if ($data['status'] == PaymentMethod::WAITING_FOR_PAYMENT) {
            unset($data['status']);
        }

        return $data;
    }

    protected function serachDiscount(string|null $code, Set $set, Get $get)
    {
        try {
            $discount = Discount::where('code', $code)->firstOrFail();

            $set('discount', $discount->discount);
            $set('total_discount', $discount->discount);
            $set('discount_code', '');
            $set('discount_code_used', $code);

            $set('total', $get('total') - $discount->discount);

            Notification::make()
                ->title('Discount ditemukan')
                ->success()
                ->body('Form berhasil diisi otomatis.')
                ->send();
        } catch (\Throwable $th) {
            $set('discount', 0);
            $set('total_discount', 0);
            $set('discount_code', '');
            $set('discount_code_used', '');

            $set('total', $get('total') - $get('discount'));

            Notification::make()
                ->title('Discount tidak ditemukan')
                ->warning()
                ->body('Mohon isi form secara manual.')
                ->send();
        }
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
                    Placeholder::make('discount')
                        ->label('Diskon')
                        ->inlineLabel()
                        ->extraAttributes(['style' => 'text-align: right; color: red'])
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
                    TextInput::make('discount_code')
                        ->label('Kode Promo atau Diskon')
                        ->hidden(function () {
                            if ($this->record->status == TransactionStatus::PAID) {
                                return true;
                            } else {
                                return false;
                            }
                        })
                        ->columnSpanFull(),
                    TextInput::make('discount_code_used')
                        ->hidden()
                        ->columnSpanFull(),
                    TextInput::make('total_discount')
                        ->hidden()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Actions::make([
                        Action::make('check_discount')
                            ->label('Cek Kode Promo atau Diskon')
                            ->action(function (Get $get, Set $set) {
                                $this->serachDiscount($get('discount_code'), $set, $get);
                            }),
                        Action::make('remove_discount')
                            ->label('Hapus Diskon')
                            ->color('danger')
                            ->action(function (Get $get, Set $set) {
                                $set('total', $get('total') + $get('discount'));

                                $set('discount', 0);
                                $set('total_discount', 0);
                                $set('discount_code', '');
                                $set('discount_code_used', '');

                                Notification::make()
                                    ->title('Discount dihapus')
                                    ->success()
                                    ->body('Form berhasil diisi otomatis.')
                                    ->send();
                            })
                    ])->fullWidth()->hidden(function () {
                        if ($this->record->status == TransactionStatus::PAID) {
                            return true;
                        } else {
                            return false;
                        }
                    }),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(PaymentMethod::getLabels())
                        ->required()
                        ->live()
                        ->disabled(function () {
                            if ($this->record->status == TransactionStatus::PAID) {
                                return true;
                            } else {
                                return false;
                            }
                        })
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
                        ->disabled(function () {
                            if ($this->record->status == TransactionStatus::PAID) {
                                return true;
                            } else {
                                return false;
                            }
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
                        ->disabled(function () {
                            if ($this->record->status == TransactionStatus::PAID) {
                                return true;
                            } else {
                                return false;
                            }
                        })
                        ->columnSpanFull(),
                    Placeholder::make('createdBy.name')
                        ->label('Cashier Name')
                        ->visible(function ($state) {
                            if ($state != null) {
                                return true;
                            } else {
                                return false;
                            }
                        })
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
                            ->label(function () {
                                if ($this->record->status == TransactionStatus::PAID) {
                                    return "Sudah dibayarkan";
                                } else {
                                    return "Bayar";
                                }
                            })
                            ->action(function ($livewire, $record, Get $get) {
                                $livewire->save();

                                DB::transaction(function () use ($get, $record) {
                                    if ($get('status') == TransactionStatus::PAID) {
                                        ClientVisit::where('id', $record->clientVisit->id)->update(['status' => VisitStatus::DONE]);
                                    }

                                    if ($get('discount') != null) {
                                        $discount = Discount::where('code', $get('discount_code_used'))->first();
                                        TransactionDiscount::create([
                                            'transaction_id' => $record->id,
                                            'discount_id' => $discount->id,
                                            'name' => $discount->name,
                                            'discount' => $discount->discount,
                                            'code' => $discount->code,
                                        ]);

                                        Transaction::where('id', $record->id)->update(['amount' => $record->amount - $discount->discount]);
                                    }
                                });

                                $this->record->refresh();
                            })
                            ->disabled(function () {
                                if ($this->record->status == TransactionStatus::PAID) {
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
