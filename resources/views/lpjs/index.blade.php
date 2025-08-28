<x-app-layout>
    {{-- <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Data LPJ BOK Puskesmas') }}
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
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold mb-2">Data LPJ BOK Puskesmas</h1>
                            <p class="text-indigo-100">Kelola dan pantau semua Laporan Pertanggungjawaban</p>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                            <a href="{{ route('lpjs.create') }}" class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-50 transition duration-200 shadow-md btn-modern">
                                <i class="fas fa-plus mr-2"></i>Buat LPJ Baru
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
                                    $lpjForDownload = \App\Models\Lpj::find(session('show_download'));
                                @endphp
                                @if ($lpjForDownload)
                            <a href="{{ route('lpj.download', $lpjForDownload) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center ml-4 transition duration-200">
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
                <!-- Total LPJ -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-file-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total LPJ</dt>
                                    <dd class="text-2xl font-bold text-gray-900 stat-number">{{ number_format($stats['total_lpjs']) }}</dd>
                                    <dd class="text-xs text-blue-600 font-medium">
                                        @if($stats['by_type']->get('SPPT', 0) > 0)
                                            {{ $stats['by_type']['SPPT'] }} SPPT
                                        @endif
                                        @if($stats['by_type']->get('SPPD', 0) > 0)
                                            {{ $stats['by_type']['SPPD'] }} SPPD
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Peserta -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-users text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Peserta</dt>
                                    <dd class="text-2xl font-bold text-gray-900 stat-number">{{ number_format($stats['total_participants']) }}</dd>
                                    <dd class="text-xs text-green-600 font-medium">Semua kegiatan</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Transport -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-car text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Transport</dt>
                                    <dd class="text-2xl font-bold text-gray-900 stat-number">
                                        Rp {{ number_format($stats['total_transport'], 0, ',', '.') }}
                                    </dd>
                                    <dd class="text-xs text-orange-600 font-medium">Biaya perjalanan</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Anggaran -->
                <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 dashboard-card">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Anggaran</dt>
                                    <dd class="text-2xl font-bold text-gray-900 stat-number">
                                        Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}
                                    </dd>
                                    <dd class="text-xs text-purple-600 font-medium">
                                        Rata-rata: Rp {{ number_format($stats['avg_per_lpj'], 0, ',', '.') }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Controls -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    
                    <!-- Search and Filters -->
                    <div class="flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" 
                                       id="searchInput"
                                       placeholder="Cari kegiatan, no surat..." 
                                       value="{{ request('search') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <div id="searchLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                                    <div class="loading-spinner"></div>
                                </div>
                            </div>
                            
                            <!-- Type Filter -->
                            <div>
                                <select id="typeFilter" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="all">Semua Tipe</option>
                                    <option value="SPPT" {{ request('type') == 'SPPT' ? 'selected' : '' }}>SPPT</option>
                                    <option value="SPPD" {{ request('type') == 'SPPD' ? 'selected' : '' }}>SPPD</option>
                                </select>
                            </div>

                            <!-- Month/Year Filter -->
                            <div class="flex gap-2">
                                <select id="monthFilter" class="flex-1 py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Bulan</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                                <select id="yearFilter" class="flex-1 py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Tahun</option>
                                    @for($year = 2023; $year <= 2026; $year++)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                    </div>

                            <!-- Per Page -->
                            <div>
                                <select id="perPageFilter" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per halaman</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per halaman</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                                </select>
                            </div>
                        </div>

                        <!-- Active Filters Display -->
                        <div id="activeFilters" class="mt-4 flex flex-wrap gap-2 {{ request()->hasAny(['search', 'type', 'month', 'year']) ? '' : 'hidden' }}">
                            <span class="text-sm text-gray-600 font-medium">Filter aktif:</span>
                            @if(request('search'))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Search: "{{ request('search') }}"
                                    <button type="button" class="ml-1 text-blue-600 hover:text-blue-800" onclick="clearFilter('search')">×</button>
                                </span>
                            @endif
                            @if(request('type') && request('type') !== 'all')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Tipe: {{ request('type') }}
                                    <button type="button" class="ml-1 text-green-600 hover:text-green-800" onclick="clearFilter('type')">×</button>
                                </span>
                            @endif
                            @if(request('month') && request('year'))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ DateTime::createFromFormat('!m', request('month'))->format('F') }} {{ request('year') }}
                                    <button type="button" class="ml-1 text-purple-600 hover:text-purple-800" onclick="clearFilter('month')">×</button>
                                </span>
                            @endif
                            <button type="button" id="clearAllFilters" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                Hapus semua filter
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="lg:w-80">
                        <div class="border-l border-gray-200 pl-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Aksi Massal</h3>
                            
                            <div class="space-y-3">
                                <!-- Show All Toggle -->
                                <div class="flex items-center justify-between">
                                    <label class="text-sm text-gray-600">Tampilkan semua</label>
                                    <button id="showAllToggle" 
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $showAll ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                            data-show-all="{{ $showAll ? 'true' : 'false' }}">
                                        <span class="sr-only">Toggle show all</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $showAll ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>

                                <!-- Bulk Selection -->
                                <div id="bulkActions" class="space-y-2 {{ $showAll || (!$showAll && $stats['total_lpjs'] > 0) ? '' : 'hidden' }}">
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm text-gray-600">
                                            <input type="checkbox" id="selectAll" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            Pilih semua
                                        </label>
                                        <span id="selectedCount" class="text-xs text-gray-500">0 dipilih</span>
                                    </div>
                                    
                                    <button id="bulkDeleteBtn" 
                                            class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                            disabled>
                                        <i class="fas fa-trash mr-2"></i>Hapus Terpilih
                                    </button>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100">
                    <div id="tableContainer">
                    @include('lpjs.partials.table', ['lpjs' => $lpjs, 'showAll' => $showAll])
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div id="bulkDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Konfirmasi Hapus Massal</h3>
                <div class="mt-4">
                    <p class="text-sm text-gray-600">
                        Anda akan menghapus <strong id="bulkDeleteCount">0</strong> LPJ yang dipilih.
                    </p>
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">
                            <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Semua data LPJ termasuk peserta dan dokumen terkait akan dihapus permanen.
                        </p>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <div class="flex space-x-3">
                        <button id="confirmBulkDeleteBtn" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-lg w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Ya, Hapus Semua
                        </button>
                        <button id="cancelBulkDeleteBtn" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
            <div class="mt-3">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100">
                            <i class="fas fa-tasks text-indigo-600"></i>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 ml-3">Aksi LPJ</h3>
                    </div>
                    <button onclick="closeActionModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- LPJ Info -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <p class="text-sm text-gray-600 mb-2"><strong>No. Surat:</strong> <span id="action-modal-no-surat"></span></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Kegiatan:</strong> <span id="action-modal-kegiatan" class="break-words"></span></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Jumlah Peserta:</strong> <span id="action-modal-peserta"></span> orang</p>
                    <p class="text-sm text-gray-600"><strong>Total Anggaran:</strong> <span id="action-modal-total"></span></p>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="actionView()" class="flex flex-col items-center justify-center p-4 border-2 border-indigo-200 rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-200 group">
                        <i class="fas fa-eye text-2xl text-indigo-500 mb-2 group-hover:text-indigo-600"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600">Lihat Detail</span>
                    </button>
                    
                    <button onclick="actionEdit()" class="flex flex-col items-center justify-center p-4 border-2 border-yellow-200 rounded-lg hover:border-yellow-400 hover:bg-yellow-50 transition-all duration-200 group">
                        <i class="fas fa-edit text-2xl text-yellow-500 mb-2 group-hover:text-yellow-600"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-600">Edit LPJ</span>
                    </button>
                    
                    <button onclick="actionDownload()" class="flex flex-col items-center justify-center p-4 border-2 border-green-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition-all duration-200 group">
                        <i class="fas fa-download text-2xl text-green-500 mb-2 group-hover:text-green-600"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-green-600">Download</span>
                    </button>
                    
                    <button onclick="actionDelete()" class="flex flex-col items-center justify-center p-4 border-2 border-red-200 rounded-lg hover:border-red-400 hover:bg-red-50 transition-all duration-200 group">
                        <i class="fas fa-trash text-2xl text-red-500 mb-2 group-hover:text-red-600"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-red-600">Hapus LPJ</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Konfirmasi Hapus LPJ</h3>
                <div class="mt-4 text-left">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-2"><strong>No. Surat:</strong> <span id="modal-no-surat"></span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Kegiatan:</strong> <span id="modal-kegiatan" class="break-words"></span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Jumlah Peserta:</strong> <span id="modal-peserta"></span> orang</p>
                        <p class="text-sm text-gray-600"><strong>Total Anggaran:</strong> <span id="modal-total"></span></p>
                    </div>
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">
                            <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <div class="flex space-x-3">
                        <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-lg w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Ya, Hapus LPJ
                        </button>
                        <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Form -->
    <form id="bulkDeleteForm" action="{{ route('lpjs.bulk-delete') }}" method="POST" style="display: none;">
        @csrf
        <div id="bulkDeleteInputs"></div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            const searchInput = document.getElementById('searchInput');
            const searchLoading = document.getElementById('searchLoading');
            const activeFilters = document.getElementById('activeFilters');
            const tableContainer = document.getElementById('tableContainer');
            const typeFilter = document.getElementById('typeFilter');
            const monthFilter = document.getElementById('monthFilter');
            const yearFilter = document.getElementById('yearFilter');
            const perPageFilter = document.getElementById('perPageFilter');
            const showAllToggle = document.getElementById('showAllToggle');
            const selectAll = document.getElementById('selectAll');
            const selectedCount = document.getElementById('selectedCount');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            
            let searchTimeout;
            let selectedLpjs = new Set();
            
            // Show All Toggle
            showAllToggle.addEventListener('click', function() {
                const isShowAll = this.dataset.showAll === 'true';
                const newShowAll = !isShowAll;
                
                // Update toggle appearance
                this.dataset.showAll = newShowAll.toString();
                if (newShowAll) {
                    this.classList.add('bg-indigo-600');
                    this.classList.remove('bg-gray-200');
                    this.querySelector('span:last-child').classList.add('translate-x-6');
                    this.querySelector('span:last-child').classList.remove('translate-x-1');
                } else {
                    this.classList.remove('bg-indigo-600');
                    this.classList.add('bg-gray-200');
                    this.querySelector('span:last-child').classList.remove('translate-x-6');
                    this.querySelector('span:last-child').classList.add('translate-x-1');
                }
                
                // Apply filter
                applyFilters();
            });

            // Filter functions
            function applyFilters() {
                showLoading();
                
                const params = new URLSearchParams();
                
                // Get current filter values
                if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
                if (typeFilter.value && typeFilter.value !== 'all') params.set('type', typeFilter.value);
                if (monthFilter.value) params.set('month', monthFilter.value);
                if (yearFilter.value) params.set('year', yearFilter.value);
                if (perPageFilter.value && perPageFilter.value !== '10') params.set('per_page', perPageFilter.value);
                if (showAllToggle.dataset.showAll === 'true') params.set('show_all', 'true');
                
                // Make request
                fetch(`{{ route('lpjs.index') }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    updateActiveFilters();
                    resetBulkSelection();
                    hideLoading();
                })
                .catch(error => {
                    console.error('Filter error:', error);
                    hideLoading();
                });
            }

            function clearFilter(filterName) {
                switch(filterName) {
                    case 'search':
                        searchInput.value = '';
                        break;
                    case 'type':
                        typeFilter.value = 'all';
                        break;
                    case 'month':
                        monthFilter.value = '';
                        yearFilter.value = '';
                        break;
                }
                applyFilters();
            }

            function showLoading() {
                searchLoading.classList.remove('hidden');
            }

            function hideLoading() {
                    searchLoading.classList.add('hidden');
            }

            function updateActiveFilters() {
                const hasFilters = searchInput.value.trim() || 
                                 (typeFilter.value && typeFilter.value !== 'all') || 
                                 monthFilter.value || yearFilter.value;
                
                if (hasFilters) {
                    activeFilters.classList.remove('hidden');
                } else {
                    activeFilters.classList.add('hidden');
                }
            }

            function resetBulkSelection() {
                selectedLpjs.clear();
                selectAll.checked = false;
                updateBulkActionState();
                
                // Uncheck all individual checkboxes
                document.querySelectorAll('.lpj-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }

            function updateBulkActionState() {
                selectedCount.textContent = `${selectedLpjs.size} dipilih`;
                bulkDeleteBtn.disabled = selectedLpjs.size === 0;
            }

            // Event listeners
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 300);
            });
            
            [typeFilter, monthFilter, yearFilter, perPageFilter].forEach(filter => {
                filter.addEventListener('change', applyFilters);
            });

            document.getElementById('clearAllFilters').addEventListener('click', function() {
                searchInput.value = '';
                typeFilter.value = 'all';
                monthFilter.value = '';
                yearFilter.value = '';
                applyFilters();
            });

            // Bulk selection
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.lpj-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    if (this.checked) {
                        selectedLpjs.add(checkbox.value);
                    } else {
                        selectedLpjs.delete(checkbox.value);
                    }
                });
                updateBulkActionState();
            });

            // Handle individual checkbox changes (delegated)
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('lpj-checkbox')) {
                    if (e.target.checked) {
                        selectedLpjs.add(e.target.value);
                    } else {
                        selectedLpjs.delete(e.target.value);
                        selectAll.checked = false;
                    }
                    updateBulkActionState();
                    
                    // Update select all checkbox
                    const allCheckboxes = document.querySelectorAll('.lpj-checkbox');
                    const checkedCheckboxes = document.querySelectorAll('.lpj-checkbox:checked');
                    selectAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                }
            });

            // Bulk delete
            bulkDeleteBtn.addEventListener('click', function() {
                if (selectedLpjs.size > 0) {
                    document.getElementById('bulkDeleteCount').textContent = selectedLpjs.size;
                    document.getElementById('bulkDeleteModal').classList.remove('hidden');
                }
            });

            document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function() {
                const form = document.getElementById('bulkDeleteForm');
                const inputsContainer = document.getElementById('bulkDeleteInputs');
                
                // Clear previous inputs
                inputsContainer.innerHTML = '';
                
                // Add selected LPJ IDs
                selectedLpjs.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'lpj_ids[]';
                    input.value = id;
                    inputsContainer.appendChild(input);
                });
                
                form.submit();
            });

            document.getElementById('cancelBulkDeleteBtn').addEventListener('click', function() {
                document.getElementById('bulkDeleteModal').classList.add('hidden');
            });

            // Handle pagination links (delegated)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    const url = e.target.closest('.pagination a').href;
                    
                    showLoading();
                    
                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        resetBulkSelection();
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Pagination error:', error);
                        hideLoading();
                    });
                }
            });
            
            // Single delete functionality
            window.currentDeleteId = null;

            window.confirmDelete = function(id, noSurat, kegiatan, peserta, total) {
                window.currentDeleteId = id;
                
                document.getElementById('modal-no-surat').textContent = noSurat;
                document.getElementById('modal-kegiatan').textContent = kegiatan;
                document.getElementById('modal-peserta').textContent = peserta;
                document.getElementById('modal-total').textContent = total;
                
                document.getElementById('deleteModal').classList.remove('hidden');
            };

            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (window.currentDeleteId) {
                    document.getElementById('delete-form-' + window.currentDeleteId).submit();
                }
            });

            document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
                document.getElementById('deleteModal').classList.add('hidden');
                window.currentDeleteId = null;
            });

            // Close modals when clicking outside or pressing Escape
            [document.getElementById('deleteModal'), document.getElementById('bulkDeleteModal'), document.getElementById('actionModal')].forEach(modal => {
                modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    window.currentDeleteId = null;
                }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    [document.getElementById('deleteModal'), document.getElementById('bulkDeleteModal'), document.getElementById('actionModal')].forEach(modal => {
                        if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        window.currentDeleteId = null;
                    }
                    });
                }
            });
        });

        // Action Modal Functions
        window.closeActionModal = function() {
            document.getElementById('actionModal').classList.add('hidden');
        };

        window.actionView = function() {
            if (window.currentActionLpjId) {
                window.location.href = `/lpjs/${window.currentActionLpjId}`;
            }
        };

        window.actionEdit = function() {
            if (window.currentActionLpjId) {
                window.location.href = `/lpjs/${window.currentActionLpjId}/edit`;
            }
        };

        window.actionDownload = function() {
            if (window.currentActionLpjId) {
                try {
                    // Fix: Use correct route pattern /lpj/ instead of /lpjs/
                    const downloadUrl = `/lpj/${window.currentActionLpjId}/download`;
                    console.log('Initiating download for LPJ ID:', window.currentActionLpjId);
                    window.location.href = downloadUrl;
                } catch (error) {
                    console.error('Download error:', error);
                    alert('Terjadi kesalahan saat mengunduh dokumen. Silakan coba lagi.');
                }
            } else {
                console.error('No LPJ ID available for download');
                alert('ID LPJ tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            }
        };

        window.actionDelete = function() {
            if (window.currentActionLpjId && window.currentActionLpjData) {
                // Close action modal
                window.closeActionModal();
                
                // Show delete confirmation modal
                document.getElementById('modal-no-surat').textContent = window.currentActionLpjData.noSurat;
                document.getElementById('modal-kegiatan').textContent = window.currentActionLpjData.kegiatan;
                document.getElementById('modal-peserta').textContent = window.currentActionLpjData.peserta;
                document.getElementById('modal-total').textContent = window.currentActionLpjData.total;
                
                // Set current delete ID
                window.currentDeleteId = window.currentActionLpjId;
                
                // Show delete modal
                document.getElementById('deleteModal').classList.remove('hidden');
            }
        };
    </script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</x-app-layout>