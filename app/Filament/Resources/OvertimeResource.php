<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeResource\Pages;
use App\Filament\Resources\OvertimeResource\RelationManagers;
use App\Models\Overtime;
use App\Models\OvertimeSummary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\ActionsPosition;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OvertimeRequestMail;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;
    public static function canCreate(): bool { return false; }
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('initial')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cluster')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('role')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('start_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->required(),
                Forms\Components\TextInput::make('total_jam')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_lembur')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved_supervisor',
                        'success' => 'approved_manager',
                        'danger'  => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock'       => 'pending',             // ⏰ pending
                        'heroicon-o-user-check'  => 'approved_supervisor', // ✅ supervisor approve
                        'heroicon-o-check'       => 'approved_manager',    // ✅ manager approve
                        'heroicon-o-x-mark'      => 'rejected', 
                    ]),
                Tables\Columns\TextColumn::make('initial')->searchable(),
                Tables\Columns\TextColumn::make('nama')->searchable(),
                Tables\Columns\TextColumn::make('activity')
                    ->label('Activity')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->activity?->activity ?? '-') // ambil kolom activity dari relasi
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->activity?->activity ?? '-'),
                Tables\Columns\TextColumn::make('start_date')->dateTime('d-m-Y H:i')->searchable(),
                Tables\Columns\TextColumn::make('end_date')->dateTime('d-m-Y H:i')->searchable(),
                Tables\Columns\TextColumn::make('total_jam')->label('Total Jam')->searchable(),
                Tables\Columns\TextColumn::make('total_lembur')->label('Total Lembur')->searchable(),
                
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('bulan')
                    ->label('Filter Bulan')
                    ->options(function () {
                        // Ambil semua bulan dari data overtime, format YYYY-MM
                        return Overtime::query()
                            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as bulan")
                            ->distinct()
                            ->pluck('bulan', 'bulan')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $bulan = $data['value'];
                            [$year, $month] = explode('-', $bulan);
                            $query->whereYear('start_date', $year)
                                  ->whereMonth('start_date', $month);
                        }
                    }),
            ])
            ->defaultSort('start_date', 'asc')
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('')
                    ->color('success')
                    ->icon('heroicon-s-check-circle')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Lembur')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui lembur ini?')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->action(function ($record) {
                        $user = auth()->user();
                        $role = $user->roles()->first()?->name;

                        // ✅ Role yang bisa approve semua: super_admin + admin
                        if (in_array($role, ['super_admin', 'admin'])) {
                            $record->update(['status' => 'approved', 'approved_by' => $user->id]);
                            // Kirim email langsung ke USER
                            Mail::to($record->user->email)->queue(new OvertimeRequestMail($record, $record->nama));

                        } 
                        // ✅ Manager hanya bisa approve supervisor_approved
                        elseif ($role === 'manager' && $record->status === 'Engineer_approved') {
                            $record->update(['status' => 'approved', 'approved_by' => $user->id]);
                            // Kirim email ke USER yang mengajukan
                            Mail::to($record->user->email)->queue(new OvertimeRequestMail($record, $record->nama));

                        } 
                        // ✅ Supervisor hanya bisa approve pending
                        elseif ($role === 'supervisor' && $record->status === 'pending') {
                            $record->update(['status' => 'Engineer_approved', 'approved_by' => $user->id]);
                            // Kirim email ke MANAGER
                            $manager = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
                            if ($manager) {
                                Mail::to($manager->email)->queue(new OvertimeRequestMail($record, 'Manager'));
                            }
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Anda tidak punya hak approve atau status belum sesuai')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Hanya update summary saat approved_manager
                        if ($record->status === 'approved') {
                            $start = \Carbon\Carbon::parse($record->start_date);
                            $summary = \App\Models\OvertimeSummary::firstOrNew([
                                'initial' => $record->initial,
                                'bulan' => $start->format('Y-m'),
                            ]);
                            $summary->nama = $record->nama;
                            $summary->total_jam = \App\Models\Overtime::where('initial', $record->initial)
                                ->where('status', 'approved')
                                ->whereMonth('start_date', $start->month)
                                ->whereYear('start_date', $start->year)
                                ->sum('total_jam');
                            $summary->total_lembur = \App\Models\Overtime::where('initial', $record->initial)
                                ->where('status', 'approved')
                                ->whereMonth('start_date', $start->month)
                                ->whereYear('start_date', $start->year)
                                ->sum('total_lembur');
                            $summary->activity_id = $record->activity_id;
                            $summary->save();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('✔ Lembur berhasil diapprove')
                            ->success()
                            ->send();
                        
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('')
                    ->color('danger')
                    ->icon('heroicon-c-x-circle')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Lembur')
                    ->modalDescription('Apakah Anda yakin ingin menolak lembur ini?')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->action(function ($record) {
                        $user = auth()->user();
                        $role = $user->roles()->first()?->name;

                        // Role yang bisa reject semua: super_admin + admin
                        if (!in_array($role, ['super_admin', 'admin', 'manager', 'supervisor'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Anda tidak memiliki izin untuk reject lembur ini.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update(['status' => 'rejected', 'approved_by' => $user->id]);
                        // Kirim email ke USER
                        Mail::to($record->user->email)->queue(new OvertimeRequestMail($record, $record->nama, 'reject'));

                        $start = \Carbon\Carbon::parse($record->start_date);

                        // Hapus summary lama untuk bulan & initial
                        \App\Models\OvertimeSummary::where('initial', $record->initial)
                            ->where('bulan', $start->format('Y-m'))
                            ->delete();

                        // Hitung ulang summary berdasarkan lembur approved yang tersisa
                        $approved = \App\Models\Overtime::where('initial', $record->initial)
                            ->where('status', 'approved')
                            ->whereMonth('start_date', $start->month)
                            ->whereYear('start_date', $start->year);

                        if ($approved->count() > 0) {
                            $summary = new \App\Models\OvertimeSummary();
                            $summary->initial = $record->initial;
                            $summary->nama = $record->nama;
                            $summary->bulan = $start->format('Y-m');
                            $summary->total_jam = $approved->sum('total_jam');
                            $summary->total_lembur = $approved->sum('total_lembur');
                            $summary->activity_id = $record->activity_id; // optional terakhir
                            $summary->save();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('❌ Lembur ditolak & summary diperbarui')
                            ->danger()
                            ->send();
                    }), 
                ], position: ActionsPosition::BeforeColumns)
                ->actionsColumnLabel('Action')
            
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
            'index' => Pages\ListOvertimes::route('/'),
            'create' => Pages\CreateOvertime::route('/create'),
            'view' => Pages\ViewOvertime::route('/{record}'),
            'edit' => Pages\EditOvertime::route('/{record}/edit'),
        ];
    }
}
