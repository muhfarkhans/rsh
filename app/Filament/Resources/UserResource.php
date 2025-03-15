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
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('email')
                    ->label('Email')
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
                SelectFilter::make('roles.name')
                    ->label('Roles')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas(
                                'roles',
                                fn(Builder $query) => $query->where('role_id', '=', $data['value'])
                            );
                        }
                    })
                    ->options(function () {
                        return Role::get()->pluck('name', 'id');
                    })
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
                // SoftDeletingScope::class,
            ]);
    }
}
