<x-app-layout>
    {{-- <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Detail Pegawai') }}
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
                <div class="bg-gradient-to-r from-teal-600 to-cyan-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold mb-2 flex items-center">
                                <i class="fas fa-user-circle mr-3"></i>Detail Pegawai BOK
                            </h1>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-teal-100">
                                <div>
                                    <p class="text-sm opacity-80">Nama Pegawai</p>
                                    <p class="text-lg font-semibold">{{ $employee->nama }}</p>
                                </div>
                                <div>
                                    <p class="text-sm opacity-80">NIP</p>
                                    <p class="text-lg font-mono">{{ $employee->nip }}</p>
                                </div>
                                <div>
                                    <p class="text-sm opacity-80">Pangkat/Golongan</p>
                                    <p class="text-lg font-semibold">{{ $employee->pangkat_golongan }}</p>
                                </div>
                                <div>
                                    <p class="text-sm opacity-80">Jabatan</p>
                                    <p class="text-lg">{{ $employee->jabatan ?? 'Tidak ada' }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-teal-200">
                                <span class="flex items-center">
                                    <i class="fas fa-birthday-cake mr-2"></i>{{ $employee->tanggal_lahir->format('d F Y') }} ({{ $employee->tanggal_lahir->age }} tahun)
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i>{{ $employee->lpjParticipants->count() }} kegiatan LPJ
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-money-bill-wave mr-2"></i>Total: Rp {{ number_format($employee->lpjParticipants->sum('total_amount'), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 lg:mt-0 flex flex-wrap gap-3">
                            <a href="{{ route('employees.edit', $employee) }}" class="bg-white text-teal-600 px-4 py-2 rounded-lg font-semibold hover:bg-teal-50 transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-edit mr-2"></i>Edit Data
                            </a>
                            <a href="{{ route('employees.index') }}" class="bg-teal-800 text-white px-4 py-2 rounded-lg font-semibold hover:bg-teal-900 transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Employee Information Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10 bg-teal-500 text-white rounded-xl">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900">Informasi Pegawai</h3>
                                    <p class="text-sm text-gray-600">Data lengkap pegawai yang terdaftar</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>Nama Lengkap
                                    </label>
                                    <p class="text-gray-900 font-medium text-lg">{{ $employee->nama }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-id-card text-purple-500 mr-2"></i>NIP
                                    </label>
                                    <p class="text-gray-900 font-mono text-lg">{{ $employee->nip }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-calendar text-orange-500 mr-2"></i>Tanggal Lahir
                                    </label>
                                    <p class="text-gray-900 font-medium">{{ $employee->tanggal_lahir->format('d F Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ $employee->tanggal_lahir->age }} tahun</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-award text-yellow-500 mr-2"></i>Pangkat/Golongan
                                    </label>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                        {{ $employee->pangkat_golongan }}
                                    </span>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-briefcase text-indigo-500 mr-2"></i>Jabatan
                                    </label>
                                    <p class="text-gray-900 font-medium">{{ $employee->jabatan ?? 'Tidak ada jabatan yang tercatat' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LPJ Participation History -->
                    @if($employee->lpjParticipants->count() > 0)
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 bg-green-500 text-white rounded-xl">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-bold text-gray-900">Riwayat Kegiatan LPJ</h3>
                                            <p class="text-sm text-gray-600">Daftar kegiatan yang pernah diikuti</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-green-600">{{ $employee->lpjParticipants->count() }}</div>
                                        <div class="text-xs text-gray-500">kegiatan</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kegiatan</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipe</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Transport</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Uang Harian</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($employee->lpjParticipants->sortByDesc('created_at') as $index => $participation)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-lg text-xs font-bold mr-3">
                                                            {{ $index + 1 }}
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-semibold text-gray-900">
                                                                <a href="{{ route('lpjs.show', $participation->lpj) }}" class="hover:text-blue-600 transition-colors duration-150">
                                                                    {{ $participation->lpj->no_surat }}
                                                                </a>
                                                            </div>
                                                            <div class="text-xs text-gray-500">{{ $participation->lpj->kegiatan }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                                        {{ $participation->lpj->type == 'SPPT' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ $participation->lpj->type }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                                        {{ $participation->role == 'KETUA' ? 'bg-red-100 text-red-800' : 
                                                           ($participation->role == 'ANGGOTA' ? 'bg-yellow-100 text-yellow-800' : 
                                                           ($participation->role == 'PENDAMPING' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                                        {{ $participation->role }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        Rp {{ number_format($participation->transport_amount, 0, ',', '.') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        Rp {{ number_format($participation->perdiem_amount, 0, ',', '.') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-green-600">
                                                        Rp {{ number_format($participation->total_amount, 0, ',', '.') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900">
                                                Total Keseluruhan:
                                            </td>
                                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                                Rp {{ number_format($employee->lpjParticipants->sum('transport_amount'), 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                                Rp {{ number_format($employee->lpjParticipants->sum('perdiem_amount'), 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm font-bold text-green-600">
                                                Rp {{ number_format($employee->lpjParticipants->sum('total_amount'), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                            <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Kegiatan</h3>
                            <p class="text-gray-500 mb-4">Pegawai ini belum pernah mengikuti kegiatan LPJ.</p>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Quick Stats -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-teal-500 to-cyan-600 text-white p-4 rounded-t-2xl">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-chart-bar mr-2"></i>Statistik Ringkas
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-3"></i>
                                    <span class="text-sm font-medium text-gray-600">Total Kegiatan</span>
                                </div>
                                <span class="text-lg font-bold text-blue-600">{{ $employee->lpjParticipants->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-car text-green-500 mr-3"></i>
                                    <span class="text-sm font-medium text-gray-600">Total Transport</span>
                                </div>
                                <span class="text-lg font-bold text-green-600">Rp {{ number_format($employee->lpjParticipants->sum('transport_amount'), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-utensils text-orange-500 mr-3"></i>
                                    <span class="text-sm font-medium text-gray-600">Total Uang Harian</span>
                                </div>
                                <span class="text-lg font-bold text-orange-600">Rp {{ number_format($employee->lpjParticipants->sum('perdiem_amount'), 0, ',', '.') }}</span>
                            </div>
                            <hr>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-teal-500 mr-3"></i>
                                    <span class="text-sm font-bold text-gray-800">Total Keseluruhan</span>
                                </div>
                                <span class="text-xl font-bold text-teal-600">Rp {{ number_format($employee->lpjParticipants->sum('total_amount'), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-t-2xl border-b border-gray-200">
                            <h3 class="font-bold text-gray-900 flex items-center">
                                <i class="fas fa-bolt mr-2"></i>Aksi Cepat
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <a href="{{ route('employees.edit', $employee) }}" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-edit mr-2"></i>Edit Pegawai
                            </a>
                            <a href="{{ route('employee-saldo.show', $employee) }}" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-money-bill-wave mr-2"></i>Lihat Saldo
                            </a>
                            <a href="{{ route('lpjs.create') }}" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-plus mr-2"></i>Buat LPJ Baru
                            </a>
                            <a href="{{ route('employees.index') }}" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 shadow-md btn-modern flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Employee Meta Info -->
                    <div class="bg-gradient-to-br from-teal-50 to-cyan-100 border border-teal-200 rounded-2xl p-4">
                        <h3 class="font-bold text-teal-900 mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>Informasi Sistem
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-teal-700 font-medium">Dibuat:</span>
                                <span class="text-teal-900">{{ $employee->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-teal-700 font-medium">Diperbarui:</span>
                                <span class="text-teal-900">{{ $employee->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-teal-700 font-medium">ID Pegawai:</span>
                                <span class="text-teal-900 font-mono">#{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
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