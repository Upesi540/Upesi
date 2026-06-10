<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Affichage de la table --}}
        <div>
            {{ $this->table }}
        </div>
    </div>

    {{-- CRUCIAL : Permet l'affichage des modals d'actions --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
