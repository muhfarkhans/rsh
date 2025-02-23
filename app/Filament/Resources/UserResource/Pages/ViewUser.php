<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Helpers\FilamentHelper;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

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
                                    ->columnSpanFull()
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
