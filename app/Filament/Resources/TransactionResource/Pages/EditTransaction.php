<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Constants\PaymentMethod;
use App\Constants\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
        $this->transaction = Transaction::where('id', $data['id'])->first();

        $client = $this->transaction->clientVisit->client;
        $items = $this->transaction->items->toArray();

        $data['client_name'] = $client->name;
        $data['client_phone'] = $client->phone;
        $data['client_job'] = $client->job;
        $data['client_address'] = $client->address;
        $data['client_visit_created_at'] = $this->transaction->clientVisit->created_at;
        $data['client_services'] = [
            [
                'service_name' => $items[0]['name'],
                'service_therapy' => $this->transaction->clientVisit->therapy->name,
                'service_price' => $items[0]['price'],
            ]
        ];
        $data['total'] = $items[0]['price'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $newData = [
            'payment_method' => $data['payment_method'],
            'status' => $data['status'],
        ];

        return $newData;
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
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->columns(2)
                                ->columnSpanFull(),
                        ])->columnSpanFull(),
                    ])->columnSpan(2),
                Section::make('Pembayaran')->schema([
                    Placeholder::make('total')
                        ->label('Total')
                        ->content(function ($state) {
                            return new HtmlString(
                                '
                                <h1>Rp. ' . $state . '</h1>
                                '
                            );
                        }),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(PaymentMethod::getLabels())
                        ->required()
                        ->columnSpanFull(),
                    Select::make('status')
                        ->label('Status')
                        ->options(TransactionStatus::getLabels())
                        ->required()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Actions::make([
                        Action::make('Save')
                            ->action(fn($livewire) => $livewire->save())
                    ])->fullWidth(),
                ])->columnSpan(1),
            ])
        ];
    }
}
