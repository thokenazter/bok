<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold mb-2 flex items-center">
                                <i class="fas fa-route mr-3"></i>Data Tiba Berangkat
                            </h1>
                            <p class="text-emerald-100">Kelola dokumen tiba berangkat untuk kunjungan desa</p>
                            <div class="mt-3 flex items-center text-sm text-emerald-200">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span>{{ $tibaBerangkats->total() }} dokumen terdaftar</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar mr-2"></i>
                                <span>{{ Carbon\Carbon::now()->format('d F Y') }}</span>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                            <a href="{{ route('tiba-berangkats.create') }}" class="bg-white text-emerald-600 px-6 py-2 rounded-lg font-semibold hover:bg-emerald-50 transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-plus mr-2"></i>Buat Tiba Berangkat
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-6 flex items-center justify-between shadow-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-500"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    @if (session('show_download'))
                        @php
                            $tibaBerangkatForDownload = \App\Models\TibaBerangkat::find(session('show_download'));
                        @endphp
                        @if ($tibaBerangkatForDownload)
                            <a href="{{ route('tiba-berangkats.download', $tibaBerangkatForDownload) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center ml-4 transition duration-200 shadow-md">
                                <i class="fas fa-download mr-2"></i>Download Dokumen
                            </a>
                        @endif
                    @endif
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-2xl mb-6 flex items-center shadow-lg">
                    <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Documents -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-file-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Dokumen</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $tibaBerangkats->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Desa -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Kunjungan</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $tibaBerangkats->sum(function($tb) { return $tb->details->count(); }) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Month -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Bulan Ini</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $tibaBerangkats->where('created_at', '>=', now()->startOfMonth())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-clock text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Minggu Ini</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $tibaBerangkats->where('created_at', '>=', now()->startOfWeek())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">
                <div class="p-6">
                    <!-- Table Header -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Dokumen Tiba Berangkat</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-file-alt mr-2"></i>No. Surat
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-map-marker-alt mr-2"></i>Jumlah Desa
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-list mr-2"></i>Desa Kunjungan
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-calendar mr-2"></i>Dibuat
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cogs mr-2"></i>Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($tibaBerangkats as $tibaBerangkat)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                                        <i class="fas fa-file-alt text-emerald-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $tibaBerangkat->no_surat }}</div>
                                                    <div class="text-sm text-gray-500">Dokumen Resmi</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $tibaBerangkat->details->count() }} Desa
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($tibaBerangkat->details as $detail)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                                        {{ $detail->pejabatTtd->desa }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                                {{ $tibaBerangkat->created_at->format('d M Y') }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $tibaBerangkat->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('tiba-berangkats.show', $tibaBerangkat) }}" 
                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition duration-150">
                                                    <i class="fas fa-eye mr-1"></i>Lihat
                                                </a>
                                                <a href="{{ route('tiba-berangkats.edit', $tibaBerangkat) }}" 
                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition duration-150">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </a>
                                                <a href="{{ route('tiba-berangkats.download', $tibaBerangkat) }}" 
                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 transition duration-150">
                                                    <i class="fas fa-download mr-1"></i>Download
                                                </a>
                                                <form action="{{ route('tiba-berangkats.destroy', $tibaBerangkat) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition duration-150"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash mr-1"></i>Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data</h3>
                                                <p class="text-gray-500 mb-4">Belum ada dokumen tiba berangkat yang dibuat.</p>
                                                <a href="{{ route('tiba-berangkats.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition duration-150">
                                                    <i class="fas fa-plus mr-2"></i>Buat Dokumen Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tibaBerangkats->hasPages())
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            {{ $tibaBerangkats->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>