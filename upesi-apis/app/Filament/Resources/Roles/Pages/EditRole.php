<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    public Collection $permissions;

    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. On définit la liste des champs qui NE SONT PAS des permissions
        $excludedFields = [
            'name',
            'guard_name',
            'display_name',
            'description',
            'select_all',
            Utils::getTenantModelForeignKey()
        ];

        // 2. On isole les permissions en filtrant tout ce qui n'est pas dans $excludedFields
        $this->permissions = collect($data)
            ->filter(fn (mixed $value, string $key): bool => ! in_array($key, $excludedFields))
            ->flatten()
            ->filter(fn ($permission): bool => filled($permission)) // Sécurité : on supprime les nulls
            ->unique();

        // 3. On prépare les données à sauvegarder en incluant tes nouveaux champs
        $fieldsToSave = ['name', 'guard_name', 'display_name', 'description'];

        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            return Arr::only($data, array_merge($fieldsToSave, [Utils::getTenantModelForeignKey()]));
        }

        return Arr::only($data, $fieldsToSave);
    }

    protected function afterSave(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function (string $permission) use ($permissionModels): void {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        // @phpstan-ignore-next-line
        $this->record->syncPermissions($permissionModels);
    }
}
