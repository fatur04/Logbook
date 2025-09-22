<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\Cluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
//use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ToggleButtons;
use Carbon\Carbon;
use App\Exports\ActivityExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Tables\Filters\Filter;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public ?string $filterMonth = null;
    public ?array $filterClusters = [];
    public ?string $filterStartDate = null;
    public ?string $filterEndDate = null;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Super admin, supervisor, dan manager bisa lihat semua
        if (!$user->hasAnyRole(['super_admin', 'supervisor', 'manager'])) {
            $query->where('initial', $user->initial);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('initial')
                    ->default(fn () => auth()->user()->initial)
                    ->disabled()
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->required(),
                Forms\Components\TextInput::make('nama')
                    ->default(fn () => auth()->user()->name)
                    ->disabled()
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->required(),
                Select::make('cluster')
                    ->label('Cluster')
                    ->options(fn () =>
                        Cluster::query()
                            ->select('cluster')
                            ->distinct()
                            ->pluck('cluster', 'cluster')
                            ->toArray()
                    )
                    ->searchable()
                    ->reactive() // ⬅️ buat dependent dropdown
                    ->required(),

                Select::make('role')
                    ->label('Role')
                    ->options(function (callable $get) {
                        $cluster = $get('cluster'); // ambil cluster yang dipilih
                        if (!$cluster) return [];

                        return Cluster::query()
                            ->where('cluster', $cluster)
                            ->select('role')
                            ->distinct()
                            ->pluck('role', 'role')
                            ->toArray();
                    })
                    ->searchable()
                    ->reactive()
                    ->required(),

                Select::make('aplikasi')
                    ->label('Aplikasi')
                    ->options(function (callable $get) {
                        $cluster = $get('cluster'); 
                        if (!$cluster) return [];

                        return Cluster::query()
                            ->where('cluster', $cluster)
                            ->pluck('aplikasi', 'aplikasi');
                            //->toArray() + ['other' => '➕ Ketik manual'];
                    })
                    ->searchable()
                    ->reactive()
                    ->required(),

                Forms\Components\TextInput::make('aplikasi_manual')
                    ->label('Aplikasi (Manual)')
                    ->placeholder('Masukkan aplikasi baru')
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->visible(fn (callable $get) => $get('aplikasi') === 'other'),

                Forms\Components\DateTimePicker::make('start_date')
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && Carbon::parse($state)->diffInDays(Carbon::now()) > 3) {
                            $set('hitung_lembur', 0);
                        }
                    }),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('End')
                    ->required()
                    ->after('start_date'),
                // 🔥 Tombol aksi harus dibungkus dengan `Actions`
                // 🔘 Toggle Button Hitung Lembur
                ToggleButtons::make('hitung_lembur')
                    ->label('Hitung Lembur?')
                    ->inline()
                    ->options([
                        0 => 'Tidak',
                        1 => 'Ya',
                    ])
                    ->colors([
                        0 => 'danger',
                        1 => 'success',
                    ])
                    ->icons([
                        0 => 'heroicon-o-x-circle',
                        1 => 'heroicon-o-check-circle',
                    ])
                    ->default(0) // default merah (tidak hitung)
                    ->live()
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        $start = $get('start_date');
                        if ($start && Carbon::parse($start)->diffInDays(Carbon::now()) > 3) {
                            $set('hitung_lembur', 0);
                        }
                    })
                    ->disabled(function (callable $get) {
                        $start = $get('start_date');
                        return $start && Carbon::parse($start)->diffInDays(Carbon::now()) > 3;
                    })
                    ->helperText(function (callable $get) {
                        $start = $get('start_date');
                        if ($start && Carbon::parse($start)->diffInDays(Carbon::now()) > 3) {
                            return '⚠️ Lembur tidak bisa dihitung karena tanggal lebih dari 3 hari dari sekarang.';
                        }
                        //return 'Pilih "Ya" jika aktivitas ini dihitung sebagai lembur.';
                    }),
                Forms\Components\Textarea::make('activity')
                    ->label('Activity')
                    ->required()
                    ->rows(4) 
                    ->placeholder('Masukkan aktivitas')
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
                Tables\Columns\TextColumn::make('cluster')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aplikasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity')
                    ->limit(40) 
                    ->searchable()
                    ->tooltip(fn ($record) => $record->activity), 
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d-m-Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date('d-m-Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('activity_filters')
                ->form([
                    DatePicker::make('start')
                        ->label('Start Date'),
                    DatePicker::make('end')
                        ->label('End Date'),
                    MultiSelect::make('clusters')
                        ->label('Cluster')
                        ->options(
                            \App\Models\Activity::query()
                                ->select('cluster')
                                ->distinct()
                                ->pluck('cluster', 'cluster')
                        )
                        ->searchable()
                        ->placeholder('Pilih Cluster'),
                    MultiSelect::make('aplikasis')
                        ->label('Aplikasi')
                        ->options(
                            \App\Models\Activity::query()
                                ->select('aplikasi')
                                ->distinct()
                                ->pluck('aplikasi', 'aplikasi')
                        )
                        ->searchable()
                        ->placeholder('Pilih Aplikasi'),
                ])
                ->query(function ($query, array $data) {
                    if ($data['start']) {
                        $query->whereDate('start_date', '>=', $data['start']);
                    }
                    if ($data['end']) {
                        $query->whereDate('end_date', '<=', $data['end']);
                    }
                    if (!empty($data['clusters'])) {
                        $query->whereIn('cluster', $data['clusters']);
                    }
                    if (!empty($data['aplikasis'])) {
                        $query->whereIn('aplikasi', $data['aplikasis']);
                    }
                    return $query;
                })
                ->label('Filter Activities'),
            ])
            ->headerActions([
                Action::make('export')
                    ->icon('heroicon-s-document-arrow-down')
                    ->button()
                    ->color('success')
                    ->tooltip('Export Excel')
                    ->form([
                        // Filter start & end date
                        DatePicker::make('filterStartDate')
                            ->label('Start Date')
                            ->displayFormat('d M Y')
                            ->default(now()->startOfMonth()),

                        DatePicker::make('filterEndDate')
                            ->label('End Date')
                            ->displayFormat('d M Y')
                            ->default(now()->endOfMonth()),

                        MultiSelect::make('filterClusters')
                            ->label('Filter Cluster')
                            ->options(
                                \App\Models\Activity::query()
                                    ->select('cluster')
                                    ->distinct()
                                    ->pluck('cluster', 'cluster')
                            )
                            ->searchable()
                            ->placeholder('Pilih Cluster'),
                    ])
                    ->action(function (array $data, $record = null) {
                        $filterStartDate = $data['filterStartDate'] ?? null;
                        $filterEndDate = $data['filterEndDate'] ?? null;
                        $filterClusters = $data['filterClusters'] ?? [];

                        return Excel::download(
                            new ActivityExport($filterStartDate, $filterEndDate, $filterClusters),
                            'activities.xlsx'
                        );
                    }),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'view' => Pages\ViewActivity::route('/{record}'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan data lembur sementara di session
        session(['hitung_lembur' => request()->input('hitung_lembur')]);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        session(['hitung_lembur' => request()->input('hitung_lembur')]);
        return $data;
    }
}
