<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\RolePermission;
use App\Filament\Resources\UserTypeResource\Pages;
use App\Filament\Resources\UserTypeResource\RelationManagers;
use App\Models\UserType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserTypeResource extends Resource
{
    protected static ?string $model = UserType::class;
    protected static ?string $cluster = RolePermission::class;
    protected static ?string $label = 'Tipe Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $activeNavigationIcon = 'heroicon-s-beaker';
    protected static ?int $navigationSort = 2;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() < 2 ? 'danger' : 'info';
    }
    protected static ?string $navigationBadgeTooltip = 'Total Tipe Pengguna';
    protected static ?string $slug = 'tipe-pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pengguna')
                    ->placeholder('Pilih Pengguna')
                    ->relationship('users', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('type_id')
                    ->label('Tipe Material Pengguna')
                    ->relationship('types', 'name') // dari relasi belongsToMany
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Pengguna')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('types.name')
                    ->label('Tipe')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserTypes::route('/'),
        ];
    }
}
