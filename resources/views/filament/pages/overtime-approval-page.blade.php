<x-filament::page>
    @if($overtime)
        <div class="space-y-4">
            <h2 class="text-xl font-bold">Detail Pengajuan Lembur</h2>
            <ul class="list-disc pl-5">
                <li><strong>Nama:</strong> {{ $overtime->nama }}</li>
                <li><strong>Initial:</strong> {{ $overtime->initial }}</li>
                <li><strong>Cluster:</strong> {{ $overtime->cluster }}</li>
                <li><strong>Role:</strong> {{ $overtime->role }}</li>
                <li><strong>Task:</strong> {{ $overtime->activity?->activity ?? 'N/A' }}</li>
                <li><strong>Tanggal:</strong> {{ $overtime->start_date }} - {{ $overtime->end_date }}</li>
                <li><strong>Total Lembur:</strong> {{ $overtime->total_lembur }}</li>
                <li><strong>Status:</strong> {{ ucfirst($overtime->status) }}</li>
                <li><strong>Approved By:</strong> {{ $overtime->approvedByUser?->name ?? '-' }}</li>
            </ul>

            @if(in_array($overtime->status, ['pending','Engineer_approved']))
                <div class="flex space-x-3">
                    <x-filament::button color="success" wire:click="approve">
                        ✅ Approve
                    </x-filament::button>
                    
                    <x-filament::button color="danger" wire:click="reject">
                        ❌ Reject
                    </x-filament::button>
                </div>
            @endif
        </div>
    @else
        <p class="text-red-500">Token tidak valid atau lembur tidak ditemukan.</p>
    @endif
</x-filament::page>
