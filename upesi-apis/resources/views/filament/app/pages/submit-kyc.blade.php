<x-filament-panels::page>
    <div class="mx-auto w-full max-w-4xl">

        <form class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end mt-6" style="margin-top: 15px">
                {{-- On appelle l'action 'submitKyc' définie dans le PHP --}}
                {{ ($this->submitKycAction)(['label' => 'Soumettre mon dossier Upesi']) }}
            </div>
        </form>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
