<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class PointSkeleton extends Field
{
    protected string $view = 'forms.components.point-skeleton';

    protected string|Closure|null $imageUrl = '';

    protected array|Closure|null $points = null;

    public function getImageUrl(): string|null
    {
        return $this->evaluate($this->imageUrl);
    }

    public function imageUrl(string|Closure|null $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getPoints(): array|null
    {
        return $this->evaluate($this->points);
    }

    public function points(array|Closure|null $points): static
    {
        $this->points = $points;

        return $this;
    }
}
