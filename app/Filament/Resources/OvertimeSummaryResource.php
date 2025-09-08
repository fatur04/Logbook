<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeSummaryResource\Pages;
use App\Filament\Resources\OvertimeSummaryResource\RelationManagers;
use App\Models\OvertimeSummary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OvertimeSummaryResource extends Resource
{
    protected static ?string $model = OvertimeSummary::class;
    public static function canCreate(): bool { return false; }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('initial')->disabled(),
            Forms\Components\TextInput::make('nama')->disabled(),
            Forms\Components\TextInput::make('bulan')->disabled(),
            Forms\Components\TextInput::make('total_jam')->disabled(),
            Forms\Components\TextInput::make('total_lembur')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('initial')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bulan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_jam')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_lembur')
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
            ->defaultSort('total_lembur', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('bulan')
                    ->label('Filter Bulan')
                    ->options(function () {
                        return OvertimeSummary::query()
                            ->select('bulan')
                            ->distinct()
                            ->pluck('bulan', 'bulan')
                            ->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->where('bulan', $data['value']);
                        }
                    }),
                    Tables\Filters\SelectFilter::make('initial')
                    ->label('Filter Initial')
                    ->options(function () {
                        return OvertimeSummary::query()
                            ->select('initial')
                            ->distinct()
                            ->pluck('initial', 'initial')
                            ->toArray();
                    })
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->where('initial', $data['value']);
                        }
                    }),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListOvertimeSummaries::route('/'),
            // 'create' => Pages\CreateOvertimeSummary::route('/create'),
            // 'view' => Pages\ViewOvertimeSummary::route('/{record}'),
            // 'edit' => Pages\EditOvertimeSummary::route('/{record}/edit'),
        ];
    }
}
