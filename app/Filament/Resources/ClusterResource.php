<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClusterResource\Pages;
use App\Filament\Resources\ClusterResource\RelationManagers;
use App\Models\Cluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClusterResource extends Resource
{
    protected static ?string $model = Cluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cluster')
                    ->maxLength(255)
                    ->default(null)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Forms\Components\TextInput::make('role')
                    ->maxLength(255)
                    ->default(null)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Forms\Components\TextInput::make('aplikasi')
                    ->maxLength(255)
                    ->default(null)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cluster')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aplikasi')
                    ->searchable(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListClusters::route('/'),
            'create' => Pages\CreateCluster::route('/create'),
            'view' => Pages\ViewCluster::route('/{record}'),
            'edit' => Pages\EditCluster::route('/{record}/edit'),
        ];
    }
}
