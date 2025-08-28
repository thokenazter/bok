<x-app-layout>
    {{-- <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Detail LPJ') }} - {{ $lpj->no_surat }}
            </h2>
            <div class="text-sm text-gray-600">
                {{ Carbon\Carbon::now()->format('d F Y') }}
            </div>
        </div>
    </x-slot> --}}

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-file-alt text-2xl mr-3"></i>
                                <h1 class="text-2xl font-bold">Detail LPJ BOK</h1>
                            </div>
                            <div class="mb-3">
                                <div class="flex items-center text-lg">
                                    <span class="font-semibold mr-2">{{ $lpj->no_surat }}</span>
                                    <span class="mx-2">•</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $lpj->type == 'SPPT' ? 'bg-green-500 text-white' : 'bg-purple-500 text-white' }}">
                                        {{ $lpj->type }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-blue-100 text-lg">{{ $lpj->kegiatan }}</p>
                            <div class="mt-3 flex flex-wrap items-center text-sm text-blue-200">
                                <span class="flex items-center mr-4">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $lpj->tanggal_kegiatan }}
                                </span>
                                <span class="flex items-center mr-4">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $lpj->participants->count() }} peserta
                                </span>
                                @if($lpj->jumlah_desa_darat > 0 || $lpj->jumlah_desa_seberang > 0)
                                    <span class="flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        @if($lpj->jumlah_desa_darat > 0)
                                            {{ $lpj->jumlah_desa_darat }} desa darat
                                        @endif
                                        @if($lpj->jumlah_desa_seberang > 0)
                                            {{ $lpj->jumlah_desa_seberang }} desa seberang
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 lg:mt-0 flex flex-wrap gap-3">
                            <a href="{{ route('lpj.download', $lpj) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-download mr-2"></i>Download Word
                            </a>
                            <form action="{{ route('lpj.regenerate', $lpj) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 shadow-md btn-modern">
                                    <i class="fas fa-sync-alt mr-2"></i>Regenerate
                                </button>
                            </form>
                            <a href="{{ route('lpjs.edit', $lpj) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                            <a href="{{ route('lpjs.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-500 text-white rounded-xl">
                            <i class="fas fa-car text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Transport</p>
                            <p class="text-xl font-bold text-blue-600">
                                Rp {{ number_format($lpj->participants->sum('transport_amount'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-500 text-white rounded-xl">
                            <i class="fas fa-money-bill-wave text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Uang Harian</p>
                            <p class="text-xl font-bold text-green-600">
                                Rp {{ number_format($lpj->participants->sum('per_diem_amount'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-500 text-white rounded-xl">
                            <i class="fas fa-coins text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Anggaran</p>
                            <p class="text-xl font-bold text-purple-600">
                                Rp {{ number_format($lpj->participants->sum('total_amount'), 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-indigo-500 text-white rounded-xl">
                            <i class="fas fa-users text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Jumlah Peserta</p>
                            <p class="text-xl font-bold text-indigo-600">{{ $lpj->participants->count() }} orang</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - LPJ Information -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- LPJ Information Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-500 text-white rounded-xl">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Informasi LPJ</h3>
                                    <p class="text-sm text-gray-600">Detail lengkap laporan pertanggungjawaban</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-tag text-indigo-500 mr-2"></i>Tipe LPJ
                                        </label>
                                        <div class="flex items-center">
                                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $lpj->type == 'SPPT' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $lpj->type }}
                                            </span>
                                            <span class="ml-2 text-sm text-gray-600">
                                                {{ $lpj->type == 'SPPT' ? 'Surat Perintah Perjalanan Tugas' : 'Surat Perintah Perjalanan Dinas' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-hashtag text-purple-500 mr-2"></i>No. Surat
                                        </label>
                                        <p class="text-gray-900 font-medium">{{ $lpj->no_surat }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-calendar-alt text-green-500 mr-2"></i>Tanggal Surat
                                        </label>
                                        <p class="text-gray-900">{{ $lpj->tanggal_surat }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>Kegiatan
                                        </label>
                                        <p class="text-gray-900 font-medium">{{ $lpj->kegiatan }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-calendar-check text-orange-500 mr-2"></i>Tanggal Kegiatan
                                        </label>
                                        <p class="text-gray-900">{{ $lpj->tanggal_kegiatan }}</p>
                                    </div>

                                    @if($lpj->transport_mode)
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                                            <i class="fas fa-ship text-teal-500 mr-2"></i>Mode Transport
                                        </label>
                                        <p class="text-gray-900">{{ $lpj->transport_mode }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Desa Information -->
                            @if($lpj->jumlah_desa_darat > 0 || $lpj->jumlah_desa_seberang > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>Desa Tujuan
                                </label>
                                <div class="space-y-3">
                                    @if($lpj->jumlah_desa_darat > 0)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-map text-green-600 mr-2"></i>
                                            <span class="font-semibold text-green-800">{{ $lpj->jumlah_desa_darat }} Desa Darat</span>
                                        </div>
                                        <p class="text-green-700 ml-6">{{ $lpj->desa_tujuan_darat ?: 'Tidak ada data desa tujuan' }}</p>
                                    </div>
                                    @endif
                                    @if($lpj->jumlah_desa_seberang > 0)
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-ship text-blue-600 mr-2"></i>
                                            <span class="font-semibold text-blue-800">{{ $lpj->jumlah_desa_seberang }} Desa Seberang</span>
                                        </div>
                                        <p class="text-blue-700 ml-6">{{ $lpj->desa_tujuan_seberang ?: 'Tidak ada data desa tujuan' }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($lpj->catatan)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>Catatan
                                </label>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-yellow-800">{{ $lpj->catatan }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Participants Table -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-500 text-white rounded-xl">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-bold text-gray-900">Daftar Peserta</h3>
                                        <p class="text-sm text-gray-600">{{ $lpj->participants->count() }} orang terlibat dalam kegiatan</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-indigo-600">{{ $lpj->participants->count() }}</div>
                                    <div class="text-xs text-gray-500">Total Peserta</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Peserta</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lama Tugas</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Transport</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Uang Harian</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($lpj->participants as $index => $participant)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex items-center justify-center w-8 h-8 bg-indigo-500 text-white rounded-lg text-sm font-bold mr-3">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900">{{ $participant->employee->nama }}</div>
                                                    <div class="text-sm text-gray-500">{{ $participant->employee->pangkat_golongan }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                {{ $participant->role == 'KETUA' ? 'bg-blue-100 text-blue-800' : 
                                                   ($participant->role == 'ANGGOTA' ? 'bg-green-100 text-green-800' : 
                                                   ($participant->role == 'PENDAMPING' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $participant->role == 'KETUA' ? 'PJ' : $participant->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center text-sm text-gray-900">
                                                <i class="fas fa-calendar-day text-orange-500 mr-2"></i>
                                                {{ $participant->lama_tugas_hari }} hari
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-blue-600">
                                                Rp {{ number_format($participant->transport_amount, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($participant->per_diem_amount > 0)
                                                <div class="font-semibold text-green-600">
                                                    Rp {{ number_format($participant->per_diem_amount, 0, ',', '.') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $participant->per_diem_days }} hari × Rp {{ number_format($participant->per_diem_rate, 0, ',', '.') }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-purple-600 text-lg">
                                                Rp {{ number_format($participant->total_amount, 0, ',', '.') }}
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gradient-to-r from-indigo-50 to-purple-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900">
                                            <i class="fas fa-calculator mr-2"></i>Total Keseluruhan:
                                        </td>
                                        <td class="px-6 py-4 font-bold text-blue-700">
                                            Rp {{ number_format($lpj->participants->sum('transport_amount'), 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-green-700">
                                            Rp {{ number_format($lpj->participants->sum('per_diem_amount'), 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-purple-700 text-xl">
                                            Rp {{ number_format($lpj->participants->sum('total_amount'), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Meta Information -->
                <div class="space-y-6">
                    <!-- Document Info Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-xl">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Info Dokumen</h3>
                                    <p class="text-sm text-gray-600">Metadata dan riwayat dokumen</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>Dibuat Oleh
                                </label>
                                <p class="text-gray-900">{{ $lpj->createdBy->name ?? 'System' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-clock text-green-500 mr-2"></i>Dibuat Pada
                                </label>
                                <p class="text-gray-900">{{ $lpj->created_at->format('d F Y') }}</p>
                                <p class="text-sm text-gray-500">{{ $lpj->created_at->format('H:i') }} WIB</p>
                            </div>

                            @if($lpj->updated_at != $lpj->created_at)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-edit text-amber-500 mr-2"></i>Terakhir Diupdate
                                </label>
                                <p class="text-gray-900">{{ $lpj->updated_at->format('d F Y') }}</p>
                                <p class="text-sm text-gray-500">{{ $lpj->updated_at->format('H:i') }} WIB</p>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="fas fa-hashtag text-purple-500 mr-2"></i>ID Dokumen
                                </label>
                                <p class="text-gray-500 text-sm font-mono">{{ $lpj->id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 text-white rounded-xl">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Aksi Cepat</h3>
                                    <p class="text-sm text-gray-600">Tindakan yang tersedia</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('lpj.download', $lpj) }}" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-semibold transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-download mr-2"></i>Download Word
                            </a>
                            
                            <form action="{{ route('lpj.regenerate', $lpj) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg font-semibold transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                    <i class="fas fa-sync-alt mr-2"></i>Regenerate
                                </button>
                            </form>
                            
                            <a href="{{ route('lpjs.edit', $lpj) }}" class="w-full bg-amber-500 hover:bg-amber-600 text-white px-4 py-3 rounded-lg font-semibold transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-edit mr-2"></i>Edit LPJ
                            </a>
                            
                            <a href="{{ route('lpjs.index') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-semibold transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                            </a>
                        </div>
                    </div>

                    <!-- Summary Stats Card -->
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg text-white p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-xl">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold">Ringkasan Anggaran</h3>
                                <p class="text-indigo-100 text-sm">Total biaya keseluruhan</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-indigo-100">Transport:</span>
                                <span class="font-bold">Rp {{ number_format($lpj->participants->sum('transport_amount'), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-indigo-100">Uang Harian:</span>
                                <span class="font-bold">Rp {{ number_format($lpj->participants->sum('per_diem_amount'), 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-indigo-400 pt-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Total:</span>
                                    <span class="text-2xl font-bold">Rp {{ number_format($lpj->participants->sum('total_amount'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</x-app-layout>