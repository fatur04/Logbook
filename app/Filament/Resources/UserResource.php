<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user->hasAnyRole(['super_admin', 'supervisor', 'manager'])) {
            $query->where('initial', $user->initial);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\TextInput::make('initial')
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Forms\Components\TextInput::make('nik') // Tambahkan NIK
                    ->label('NIK')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('perusahaan')
                    ->required()
                    ->maxLength(50)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Password'),
                FileUpload::make('signature_path')
                    ->label('Tanda Tangan Digital')
                    ->image() // hanya menerima gambar
                    ->directory('signatures') // folder penyimpanan
                    ->disk('public')
                    ->maxSize(2048) // maksimal 2MB
                    ->imagePreviewHeight('100') // preview di form
                    ->required(false),
                Forms\Components\Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->visible($isSuperAdmin),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('initial')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik') // Tambahkan kolom NIK
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perusahaan')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('signature_path')
                    ->label('Tanda Tangan')
                    ->disk('public')
                    ->size(80) // panjang maksimal
                    ->extraImgAttributes(['style' => 'height:50px; width:auto; object-fit:contain;']),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->formatStateUsing(fn($state): string => str()->headline($state)), 
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
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) =>
                        $isSuperAdmin || $record->id === auth()->id()
                    ),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
