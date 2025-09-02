<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Clusters\Products\ProductsCluster;
use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Models\Suppliers;
use BackedEnum;
use Dom\Text;
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
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use UnitEnum;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Filters\SelectFilter;

class SuppliersResource extends Resource
{
    protected static ?string $model = Suppliers::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $modelLabel = 'proveedor';
    protected static ?string $pluralModelLabel = 'Proveedores';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 3;

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
                TextInput::make('contact_name')
                    ->label('Nombre de contacto'),
                TextInput::make('contact_email')
                    ->email()
                    ->label('Email de contacto')
                    ->validationMessages([
                        'email' => 'El campo email debe ser una dirección de correo electrónico válida.'
                    ]),
                TextInput::make('contact_phone')
                    ->tel()
                    ->label('Teléfono de contacto')
                    ->validationMessages([
                        'tel' => 'El campo teléfono debe ser un número de teléfono válido.'
                    ]),
                TextInput::make('address')
                    ->label('Dirección'),
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
                    ->formatStateUsing(fn($state, $record) => $record?->updater?->name ?? FacadesAuth::user()->name)
                    ->dehydrateStateUsing(fn() => FacadesAuth::id())
                    ->visibleOn('edit'),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('Notas')
                    ->rows(4)
                    ->placeholder('Escribe aquí cualquier nota adicional sobre el proveedor...')
                    ->maxLength(65535)
                    ->validationMessages([
                        'max' => 'El campo notas no debe exceder los 65535 caracteres.',
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información del Proveedor')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::Bold),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->visible(fn($record) => filled($record->description))
                            ->columnSpanFull(),
                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->imageSize('100%')
                            ->columnSpanFull()
                            ->alignCenter()
                            ->url(
                                fn($record) => asset('storage/' . $record->image)
                            )->openUrlInNewTab()
                            ->visible(fn($record) => filled($record->image))
                            ->extraAttributes([
                                'title' => 'Ver imagen',
                            ]),
                        TextEntry::make('contact_name')
                            ->label('Nombre de contacto')
                            ->icon('heroicon-o-user')
                            ->copyable()
                            ->copyMessage('Nombre copiado al portapapeles')
                            ->iconColor('success')
                            ->visible(fn($record) => filled($record->contact_name)),
                        TextEntry::make('contact_email')
                            ->label('Email de contacto')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->copyMessage('Email copiado al portapapeles')
                            ->iconColor('primary')
                            ->visible(fn($record) => filled($record->contact_email)),
                        TextEntry::make('contact_phone')
                            ->label('Teléfono de contacto')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->copyMessage('Teléfono copiado al portapapeles')
                            ->iconColor('info')
                            ->visible(fn($record) => filled($record->contact_phone)),
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->icon('heroicon-o-map-pin')
                            ->copyable()
                            ->copyMessage('Dirección copiada al portapapeles')
                            ->url(fn($record) => 'https://www.google.com/maps/search/' . urlencode($record->address))
                            ->openUrlInNewTab()
                            ->iconColor('danger')
                            ->visible(fn($record) => filled($record->address)),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->visible(fn($record) => filled($record->notes))
                            ->columnSpanFull()
                            ->icon('heroicon-o-paper-clip')
                            ->iconColor('warning')
                            ->copyable()
                            ->copyMessage('Notas copiadas al portapapeles'),
                    ]),
                Section::make('Auditoría')
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
                            ->icon('heroicon-o-pencil-square'),

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
                    ->label('Nombre')
                    ->searchable(),
                ImageColumn::make('image')
                    ->label('Logo')
                    ->circular()
                    ->toggleable()
                    ->url(fn($record) => $record->image ? asset('storage/' . $record->image) : null)
                    ->openUrlInNewTab()
                    ->extraAttributes([
                        'title' => 'Ver imagen',
                    ]),
                TextColumn::make('contact_phone')
                    ->searchable()
                    ->label('Teléfono')
                    ->alignCenter()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Teléfono copiado al portapapeles')
                    ->iconColor('info')
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('address')
                    ->searchable()
                    ->alignCenter()
                    ->copyable()
                    ->copyMessage('Dirección copiada al portapapeles')
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('danger')
                    ->label('Dirección')
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('created_by')
                    ->label('Creado por')
                    ->formatStateUsing(fn($state, $record) => $record?->creator?->name ?? 'Desconocido')
                    ->badge()
                    ->color(fn($state, $record) => $record?->creator?->is_admin ? 'success' : 'info')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md')
                    ->alignCenter(),
                TextColumn::make('updated_by')
                    ->label('Editado por')
                    ->formatStateUsing(fn($state, $record) => $record?->updater?->name ?? 'Desconocido')
                    ->badge()
                    ->toggleable()
                    ->color(fn($state, $record) => $record?->updater?->is_admin ? 'success' : 'info')
                    ->sortable()
                    ->visibleFrom('md')
                    ->alignCenter(),
                TextColumn::make('deleted_at')
                    ->label('Eliminado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado en')
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
                ])->before(fn(Suppliers $record) => $record->update([
                    'updated_by' => FacadesAuth::id(),
                ])),
                ForceDeleteAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Eliminar permanentemente',
                ]),
                RestoreAction::make()->button()->hiddenLabel()->extraAttributes([
                    'title' => 'Restaurar',
                ])->before(fn(Suppliers $record) => $record->update([
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
            'index' => ManageSuppliers::route('/'),
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
