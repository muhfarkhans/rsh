<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Helpers\FilamentHelper;
use App\Models\User;
use Auth;
use DB;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.view-user';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()->columns(3)->schema(
                    [
                        Section::make('User Data')
                            ->headerActions([
                                Action::make('edit')
                                    ->label('Edit User')
                                    ->icon('heroicon-o-pencil')
                                    ->url(function (User $record) {
                                        return UserResource::getUrl('edit', ['record' => $record]);
                                    })
                            ])
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Lengkap User')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes()),
                                TextEntry::make('email')
                                    ->label('Email User')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes()),
                                TextEntry::make('phone')
                                    ->label('No. Telepon / HP')
                                    ->getStateUsing(fn($record) => filled($record->phone) ? $record->phone : 'N/A')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes()),
                                TextEntry::make('roles.name')
                                    ->extraAttributes(FilamentHelper::textEntryExtraAttributes())
                                    ->formatStateUsing(fn($state) => str($state)->headline()),
                                TextEntry::make('created_at')->label('Created at'),
                                TextEntry::make('updated_at')->label('Last updated at'),
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('reset_password')
                                        ->requiresConfirmation()
                                        ->action(function ($record) {
                                            try {
                                                DB::transaction(function () use ($record) {
                                                    User::where('id', $record->id)->update([
                                                        'password' => bcrypt($record->email)
                                                    ]);

                                                    DB::table('logs')->insert([
                                                        'title' => "Reset Password",
                                                        "description" => json_encode([
                                                            "user" => Auth::user()->id,
                                                            "detail" => "Request to reset password to user with id " . $record->id
                                                        ]),
                                                        "created_at" => now(),
                                                        "updated_at" => now()
                                                    ]);
                                                });

                                                Notification::make()
                                                    ->title('Password berhasil direset')
                                                    ->success()
                                                    ->body('Silahkan login menggunakan password baru untuk user tersebut.')
                                                    ->send();
                                            } catch (\Throwable $th) {
                                                Notification::make()
                                                    ->title('Password gagal direset')
                                                    ->warning()
                                                    ->body('Terdapat kesalahan ketika reset password.')
                                                    ->send();
                                            }
                                        })
                                        ->label('Reset Password')
                                        ->color('danger')
                                        ->icon('heroicon-s-lock-closed')
                                        ->iconPosition(IconPosition::After),
                                ])->fullWidth(),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                        Section::make('Informasi User')
                            ->schema([

                            ])
                            ->columnSpan(1),
                    ]
                ),
            ]);
    }
}
