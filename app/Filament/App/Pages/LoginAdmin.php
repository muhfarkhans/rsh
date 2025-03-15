<?php

namespace App\Filament\App\Pages;

use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Login as BaseAuth;
use Filament\Forms\Components\Actions\Action;

class LoginAdmin extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Actions::make([
                    Action::make('or sign in as Therapist and Cashier?')
                        ->link()
                        ->url(route('filament.app.pages.dashboard'))
                ])->fullWidth(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }
}
