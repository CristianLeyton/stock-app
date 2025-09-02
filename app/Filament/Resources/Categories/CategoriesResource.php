<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Categories;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use UnitEnum;
use App\Filament\Clusters\Products\ProductsCluster;



class CategoriesResource extends Resource
{
    protected static ?string $model = Categories::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Swatch;

    protected static ?string $modelLabel = 'categoria';
    protected static ?string $pluralModelLabel = 'Categorias';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 1;


    protected static ?string $cluster = ProductsCluster::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Nombre')
                    ->unique()
                    ->validationMessages([
                        'required' => 'El campo nombre es obligatorio.',
                        'unique' => 'El nombre ya está en uso.'
                    ]),
                FileUpload::make('image')
                    ->label('Imagen')
                    ->image(),
                TextInput::make('description')
                    ->label('Descripción'),
                TextInput::make('created_by')
                    ->label('Creado por')
                    ->readOnly()
                    //guardamos el id del usuario autenticado que crea la categoria pero lo mostramos con su nombre
                    ->default(fn() => FacadesAuth::user()->name)
                    ->dehydrateStateUsing(fn() => FacadesAuth::id())
                    ->visibleOn('create'),
                TextInput::make('updated_by')
                    ->label('Actualizado por')
                    ->readOnly()
                    //guardamos el id del usuario autenticado que actualiza la categoria pero lo mostramos con su nombre
                    ->default(fn() => FacadesAuth::user()->name)
                    ->formatStateUsing(fn($state, $record) => $record?->updater?->name ?? 'Desconocido')
                    ->dehydrateStateUsing(fn() => FacadesAuth::id())
                    ->visibleOn('edit'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ComponentsSection::make('Información de la Categoría')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::Bold),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->markdown()
                            ->visible(fn($record) => filled($record->description))
                            ->columnSpanFull(),

                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->imageSize(380)
                            ->columnSpanFull()
                            ->alignCenter()
                            ->url(
                                fn($record) => asset('storage/' . $record->image)
                            )->openUrlInNewTab()
                            ->visible(fn($record) => filled($record->image))
                            ->extraAttributes([
                                'title' => 'Ver imagen',
                            ]),
                    ]),

                ComponentsSection::make('Auditoría')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Creado por')
                            ->badge()
                            ->color(fn($record) => $record->creator?->is_admin ? 'success' : 'info')
                            ->icon('heroicon-o-user-circle'),

                        TextEntry::make('created_at')
                            ->label('Fecha creación')
                            ->icon('heroicon-o-calendar-days')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updater.name')
                            ->label('Editado por')
                            ->badge()
                            ->color(fn($record) => $record->updater?->is_admin ? 'success' : 'info')
                            ->icon('heroicon-o-pencil-square')
                            ,

                        TextEntry::make('updated_at')
                            ->label('Fecha edición')
                            ->icon('heroicon-o-calendar-days')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('deleted_by')
                            ->label('Eliminado por')
                            ->badge()
                            ->color('danger')
                            ->icon('heroicon-o-trash')
                            ->state(fn($record) => $record?->updater?->name ?? 'Desconocido')
                            ->visible(fn($record) => $record->deleted_at),

                        TextEntry::make('deleted_at')
                            ->label('Fecha eliminación')
                            ->icon('heroicon-o-calendar-days')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn($record) => $record->deleted_at),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular()
                    ->url(fn($record) => asset('storage/' . $record->image))
                    ->openUrlInNewTab()
                    ->extraAttributes([
                        'title' => 'Ver imagen',
                    ]),
                TextColumn::make('created_by')
                    ->label('Creado por')
                    ->formatStateUsing(fn($state, $record) => $record?->creator?->name ?? 'Desconocido')
                    ->badge()
                    ->color(fn($state, $record) => $record?->creator?->is_admin ? 'success' : 'info')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('updated_by')
                    ->label('Editado por')
                    ->formatStateUsing(fn($state, $record) => $record?->updater?->name ?? 'Desconocido')
                    ->badge()
                    ->toggleable()
                    ->color(fn($state, $record) => $record?->updater?->is_admin ? 'success' : 'info')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Editado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('created_by')->label('Creado por')->relationship('creator', 'name'),
                SelectFilter::make('updated_by')->label('Editado por')->relationship('updater', 'name'),
            ])
            ->recordActions([
                ViewAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Ver',
                ]),
                EditAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Editar',
                ]),
                DeleteAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Eliminar',
                ])->before(fn(Categories $record) => $record->update([
                    'updated_by' => FacadesAuth::id(),
                ])),
                ForceDeleteAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Eliminar permanentemente',
                ]),
                RestoreAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Restaurar',
                ])->before(fn(Categories $record) => $record->update([
                    'updated_by' => FacadesAuth::id(),
                ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
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
