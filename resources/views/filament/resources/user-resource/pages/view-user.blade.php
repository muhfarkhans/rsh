<x-filament-panels::page>
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @endif

    @livewire('list-client-visit', ['therapyId' => $this->record->id])
</x-filament-panels::page>