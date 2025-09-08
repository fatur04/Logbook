<x-filament::widget>
    <div class="space-y-4">
        @foreach($topEmployees as $employee)
            @php $persen = ($employee->total_lembur / max(1, $employee->total_jam)) * 100; @endphp
            <div>
                <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                    <span>{{ $employee->nama }}</span>
                    <span>{{ $persen }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-indigo-500 h-3 rounded-full" style="width: {{ $persen }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament::widget>
