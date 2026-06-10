<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CreateRole extends CreateRecord
{
    public Collection $permissions;

    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Liste des champs à exclure du traitement des permissions
        $excludedFields = [
            'name',
            'guard_name',
            'display_name',
            'description',
            'select_all',
            Utils::getTenantModelForeignKey()
        ];

        // 2. Isolation des permissions (on filtre les nulls pour éviter le TypeError)
        $this->permissions = collect($data)
            ->filter(fn (mixed $value, string $key): bool => ! in_array($key, $excludedFields))
            ->flatten()
            ->filter(fn ($permission): bool => filled($permission))
            ->unique();

        // 3. Champs autorisés à être créés en base de données
        $fieldsToSave = ['name', 'guard_name', 'display_name', 'description'];

        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            return Arr::only($data, array_merge($fieldsToSave, [Utils::getTenantModelForeignKey()]));
        }

        return Arr::only($data, $fieldsToSave);
    }

    protected function afterCreate(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function (string $permission) use ($permissionModels): void {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        $this->record->syncPermissions($permissionModels);
    }
}
