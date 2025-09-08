<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Register extends BaseRegister
{
    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->maxLength(255),
                Forms\Components\TextInput::make('initial')
                    ->label('Initial')
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(User::class, 'email'),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->minLength(6),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->same('password')
                    ->required(),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'initial' => $data['initial'], // simpan initial
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Tambahkan role default "reader"
        $user->assignRole('reader');

        return $user;
    }
}
