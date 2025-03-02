<?php

namespace App\Livewire;

use Livewire\Component;

class InfoNavbar extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <p style="font-size: 10px">Masuk Sebagai</p>
            <h5 tyle="font-size: 12px; font-weight: bold">{{ str_replace('_', ' ', implode(', ', Auth::user()->getRoleNames()->toArray())) }}</h5>
        </div>
        HTML;
    }
}
