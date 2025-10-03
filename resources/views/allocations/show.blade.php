<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-6 text-white flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Detail Alokasi Kegiatan</h1>
                    <div class="flex gap-2">
                        @if(auth()->user()?->isSuperAdmin())
                            <a href="{{ route('allocations.edit', $allocation) }}" class="inline-flex items-center bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-medium shadow-sm"><i class="fas fa-edit mr-2"></i>Edit</a>
                        @endif
                        <a href="{{ route('allocations.index') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg font-medium"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100">
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Tahun</div>
                            <div class="text-lg font-semibold">{{ $allocation->budget->year }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Kegiatan</div>
                            <div class="text-lg font-semibold">{{ $allocation->rab->kegiatan }}</div>
                            <div class="text-xs text-gray-500">{{ $allocation->rab->rincian_menu }} · {{ $allocation->rab->komponen }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Alokasi</div>
                            <div class="text-lg font-semibold">Rp {{ number_format($allocation->allocated_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Realisasi (LPJ)</div>
                            <div class="text-lg font-semibold">Rp {{ number_format($realized, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Sisa</div>
                            <div class="text-lg font-semibold {{ $remaining < 0 ? 'text-red-600' : 'text-green-700' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @if($allocation->notes)
                        <div>
                            <div class="text-xs text-gray-500">Catatan</div>
                            <div class="text-sm">{{ $allocation->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
