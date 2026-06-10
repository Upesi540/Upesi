<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    // Icône représentant des dossiers de projets
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    // Labels de navigation et d'interface
    protected static ?string $navigationLabel = 'Projets Réalisés';
    protected static ?string $modelLabel = 'Projet';
    protected static ?string $pluralModelLabel = 'Projets';

    // Organisation dans le menu
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Note: J'ai changé Schema en Form pour correspondre aux standards Filament v3
     */
    public static function form(Schema $form): Schema
    {
        return ProjectForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
