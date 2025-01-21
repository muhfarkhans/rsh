<x-filament-panels::page>
    <x-filament::section>
        @php
            $createdAt = $visitCupping->clientVisit->created_at;
            $diff = $createdAt->diffForHumans();
            $date = $createdAt->format('Y-m-d H:i:s') . " ({$diff})";
        @endphp

        <p>{{ $date }}</p>

        <div style="display: flex; justify-content: space-between; margin-top: 10px">
            <div>
                <h5 style="font-size: 12px">Client</h5>
                <h1 style="font-weight: bold">{{ $visitCupping->clientVisit->client->name }}</h1>
            </div>

            <div>
                <h5 style="font-size: 12px">Therapist</h5>
                <h1 style="font-weight: bold">{{ $visitCupping->therapist->name }}</h1>
            </div>
        </div>

    </x-filament::section>

    @livewire('map-point-skeleton', [
    'imageUrl' => '/assets/images/skeleton.jpg',
    'points' => $visitCupping->points,
])
</x-filament-panels::page>