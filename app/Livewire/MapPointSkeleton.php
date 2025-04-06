<?php

namespace App\Livewire;

use Livewire\Component;
use Closure;

class MapPointSkeleton extends Component
{
    protected string $view = 'livewire.map-point-skeleton';

    public int|Closure|null $id = null;

    public string|Closure|null $filePdfname = '';

    public string|Closure|null $imageUrl = '';

    public array|Closure|null $points = null;
}
