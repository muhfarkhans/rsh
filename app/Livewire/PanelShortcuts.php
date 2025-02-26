<?php

namespace App\Livewire;

use App\Constants\Role;
use App\Providers\Filament\AdminPanelProvider;
use Auth;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

class PanelShortcuts extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function toAdminPanel(): Action
    {
        return Action::make('to_admin_panel')
            ->outlined()
            ->color('primary')
            ->label('Admin Panel')
            ->extraAttributes(['class' => 'w-full'])
            ->url(fn() => url('admin'));
    }

    public function toAppPanel(): Action
    {
        return Action::make('to_app_panel')
            ->outlined()
            ->color('primary')
            ->label('App Panel')
            ->extraAttributes(['class' => 'w-full'])
            ->url(fn() => url('app'));
    }

    public function _hiddenAction(): Action
    {
        return Action::make('hidden_action')
            ->outlined()
            ->hidden()
            ->visible(false);
    }

    public function renderAction()
    {
        $arrRoute = explode("/", str_replace(url('/'), "", url()->current()));

        if (count($arrRoute) > 0) {
            if ($arrRoute[1] == "admin") {
                return $this->toAppPanel();
            } else if ($arrRoute[1] == "app") {
                return $this->toAdminPanel();
            }
        }

        return $this->_hiddenAction();
    }

    public function isAdmin()
    {
        $isAdmin = false;
        if (in_array(Role::SUPER_ADMIN, Auth::user()->getRoleNames()->toArray())) {
            $isAdmin = true;
        }

        return $isAdmin;
    }

    public function render()
    {
        if ($this->isAdmin()) {
            return <<<'HTML'
            <div>
            {{ $this->renderAction }}
            </div>
            HTML;
        }


        return <<<'HTML'
        <div>
            
        </div>
        HTML;
    }
}
