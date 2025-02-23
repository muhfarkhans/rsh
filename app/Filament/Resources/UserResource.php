<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\FilamentHelper;
use App\Models\Discount;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Constants\Role as ConstRole;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $label = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        ConstRole::SUPER_ADMIN => 'success',
                        ConstRole::CASHIER => 'info',
                        ConstRole::THERAPIST => 'warning',
                    })
                    ->formatStateUsing(fn($state) => str($state)->headline())
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        return $record->is_active == 1 ? 'success' : 'danger';
                    })
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return (bool) $record->is_active == 1 ? 'Aktif' : 'Tidak Aktif';
                    }),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->using(function (User $user, array $data) {
                    DB::transaction(function () use ($user, $data) {
                        $user->update([
                            'name' => $data['name'],
                            'phone' => $data['phone'],
                            'email' => $data['email'],
                            'address' => $data['address'],
                        ]);
                        $user->roles()->sync($data['roles']);
                    });
                }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
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
                                        return UserResource::getUrl('edit', $record->id);
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
                        Section::make('Generate Link Reset Password')
                            ->description('Click the Button below and get the link to reset the password for this account')
                            ->schema([
                                Actions::make([
                                    Action::make('generateResetLink')
                                        ->action(function (User $record) {
                                            $token = Hash::make(Str::random(60));
                                            $record->update([
                                                'reset_token' => $token,
                                                'reset_token_expires' => now()->addHours(1),
                                            ]);
                                            $link = route('password.reset', [
                                                'token' => $token,
                                                'email' => $record->email,
                                            ]);
                                            Notification::make()
                                                ->title('Reset link generated!')
                                                ->body("The password reset link is: {$link}")
                                                ->success()
                                                ->send();
                                        })
                                        ->label('Generate Reset Link')
                                        ->color('danger')
                                        ->icon('heroicon-m-link')
                                        ->iconPosition(IconPosition::After)
                                ])->fullWidth(),
                            ])
                            ->columnSpan(1),
                    ]
                ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // private static function getFormSchema(): array
    // {
    //     return [
    //         Forms\Components\Select::make('roles')
    //             ->relationship('roles', 'name')
    //             ->getOptionLabelFromRecordUsing(fn(Role $record) => str($record->name)->headline())
    //             ->preload()
    //             ->reactive()
    //             ->required()
    //             ->columns(1),
    //         Forms\Components\TextInput::make('name')
    //             ->label('Nama Lengkap User')
    //             ->placeholder('isi nama lengkap')
    //             ->required()
    //             ->columns(1),
    //         Forms\Components\TextInput::make('phone')
    //             ->label('Enter No. Telepon / HP')
    //             ->placeholder('62******')
    //             ->unique('users', 'phone', null, true)
    //             ->required()
    //             ->regex('/^62[0-9]{9,15}$/')
    //             ->columns(1),
    //         Forms\Components\TextInput::make('email')
    //             ->label('Email')
    //             ->unique('users', 'email', null, true)
    //             ->placeholder('Email@domain.com')
    //             ->email()
    //             ->required()
    //             ->columns(1),
    //         Forms\Components\TextInput::make('password')
    //             ->label('Password')
    //             ->placeholder('isi password')
    //             ->password()
    //             ->required()
    //             ->hidden(function (Get $get, string $operation) {
    //                 if ($operation === 'edit')
    //                     return true;
    //             })
    //             ->disabled(function (Get $get, string $operation) {
    //                 if ($operation === 'edit')
    //                     return true;
    //             })
    //             ->columns(1),
    //         Forms\Components\TextInput::make('password_confirmation')
    //             ->label('Konfirmasi Password')
    //             ->placeholder('konfirmasi password')
    //             ->same('password')
    //             ->required()
    //             ->hidden(function (Get $get, string $operation) {
    //                 if ($operation === 'edit')
    //                     return true;
    //             })
    //             ->disabled(function (Get $get, string $operation) {
    //                 if ($operation === 'edit')
    //                     return true;
    //             })
    //             ->password()
    //             ->dehydrated(false)
    //             ->columns(1),
    //         Forms\Components\Textarea::make('address')
    //             ->label('Alamat')
    //             ->placeholder("Alamat lengkap")
    //             ->required()
    //             ->columnSpanFull(),
    //     ];
    // }
}
