<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @if(!$showAll || ($showAll && $lpjs->count() > 0))
                <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                    <input type="checkbox" id="selectAllInTable" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </th>
                @endif
                <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="flex items-center space-x-1">
                        <span>No. Surat</span>
                        <i class="fas fa-sort text-gray-400 cursor-pointer hover:text-gray-600" data-sort="no_surat"></i>
                    </div>
                </th>
                <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Kegiatan <span class="text-xs font-normal text-gray-400">(klik untuk aksi)</span>
                </th>
                <th class="px-4 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="flex items-center space-x-1">
                        <span>Total Anggaran</span>
                        <i class="fas fa-sort text-gray-400 cursor-pointer hover:text-gray-600" data-sort="total_amount"></i>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($lpjs as $lpj)
                <tr class="table-row-hover">
                    @if(!$showAll || ($showAll && $lpjs->count() > 0))
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="checkbox" class="lpj-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="{{ $lpj->id }}">
                    </td>
                    @endif
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <div class="text-sm font-medium text-gray-900">{{ $lpj->no_surat }}</div>
                            <div class="text-xs text-gray-500">{{ $lpj->tanggal_surat }}</div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full badge-animate
                            {{ $lpj->type == 'SPPT' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $lpj->type }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="max-w-sm">
                            <div class="text-sm font-medium text-indigo-600 hover:text-indigo-800 cursor-pointer truncate transition-colors duration-200 lpj-action-trigger" 
                                 title="{{ $lpj->kegiatan }}"
                                 data-lpj-id="{{ $lpj->id }}"
                                 data-lpj-no-surat="{{ $lpj->no_surat }}"
                                 data-lpj-kegiatan="{{ $lpj->kegiatan }}"
                                 data-lpj-peserta="{{ $lpj->participants->count() }}"
                                 data-lpj-total="Rp {{ number_format($lpj->participants->sum('total_amount'), 0, ',', '.') }}">
                                {{ $lpj->kegiatan }}
                                <i class="fas fa-external-link-alt ml-1 text-xs opacity-50"></i>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center space-x-4">
                                <span>📅 {{ $lpj->tanggal_kegiatan }}</span>
                                @if($lpj->jumlah_desa_darat > 0 || $lpj->jumlah_desa_seberang > 0)
                                    <span>
                                        @if($lpj->jumlah_desa_darat > 0)
                                            🗺️{{ $lpj->jumlah_desa_darat }}
                                        @endif
                                        @if($lpj->jumlah_desa_seberang > 0)
                                            🚢{{ $lpj->jumlah_desa_seberang }}
                                        @endif
                                    </span>
                                @endif
                                <span>👥 {{ $lpj->participants->count() }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <div class="text-sm font-bold text-green-600">
                                Rp {{ number_format($lpj->participants->sum('total_amount'), 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                T: {{ number_format($lpj->participants->sum('transport_amount')/1000, 0) }}K • 
                                H: {{ number_format($lpj->participants->sum('per_diem_amount')/1000, 0) }}K
                            </div>
                        </div>
                        <!-- Hidden form for delete action -->
                        <form id="delete-form-{{ $lpj->id }}" action="{{ route('lpjs.destroy', $lpj) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ !$showAll || ($showAll && $lpjs->count() > 0) ? '5' : '4' }}" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                @if(request()->hasAny(['search', 'type', 'month', 'year']))
                                    Tidak ada LPJ yang ditemukan
                                @else
                                    Belum ada data LPJ
                                @endif
                            </h3>
                            <p class="text-gray-500 mb-4">
                                @if(request()->hasAny(['search', 'type', 'month', 'year']))
                                    Coba ubah filter atau kata kunci pencarian
                                @else
                                    Mulai dengan membuat LPJ pertama Anda
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'type', 'month', 'year']))
                                <a href="{{ route('lpjs.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-plus mr-2"></i>Buat LPJ Baru
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination and Summary -->
@if(!$showAll && $lpjs->hasPages())
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            @if ($lpjs->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    Previous
                </span>
            @else
                <a href="{{ $lpjs->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    Previous
                </a>
            @endif

            @if ($lpjs->hasMorePages())
                <a href="{{ $lpjs->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    Next
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Menampilkan
                    <span class="font-medium">{{ $lpjs->firstItem() ?? 0 }}</span>
                    sampai
                    <span class="font-medium">{{ $lpjs->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-medium">{{ $lpjs->total() }}</span>
                    hasil
                </p>
            </div>

            <div>
                {{ $lpjs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@elseif($showAll && $lpjs->count() > 0)
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-center">
            <p class="text-sm text-gray-700">
                Menampilkan semua <span class="font-medium">{{ $lpjs->count() }}</span> LPJ
            </p>
        </div>
    </div>
@endif

<script>
    // Handle select all in table (this table only)
    document.getElementById('selectAllInTable')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.lpj-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            // Trigger change event to update parent selection
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // Global variables for action modal
    window.currentActionLpjId = null;
    window.currentActionLpjData = {};

    // Add event listeners for LPJ action triggers
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Setting up LPJ action triggers...');
        
        // Use event delegation for dynamically loaded content
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.lpj-action-trigger');
            if (trigger) {
                console.log('LPJ action trigger clicked:', trigger);
                
                // Extract data from data attributes
                const id = trigger.dataset.lpjId;
                const noSurat = trigger.dataset.lpjNoSurat;
                const kegiatan = trigger.dataset.lpjKegiatan;
                const peserta = trigger.dataset.lpjPeserta;
                const total = trigger.dataset.lpjTotal;
                
                console.log('Extracted data:', { id, noSurat, kegiatan, peserta, total });
                
                // Call showActionModal with extracted data
                window.showActionModal(id, noSurat, kegiatan, peserta, total);
            }
        });
        
        console.log('LPJ action triggers setup completed');
    });

    // Ensure showActionModal is available globally
    if (typeof window.showActionModal === 'undefined') {
        window.showActionModal = function(id, noSurat, kegiatan, peserta, total) {
            console.log('showActionModal called with parameters:', { id, noSurat, kegiatan, peserta, total });
            
            // Validate parameters
            if (!id || id === null || id === undefined) {
                console.error('Invalid ID passed to showActionModal:', id);
                alert('Error: ID LPJ tidak valid. Silakan refresh halaman.');
                return;
            }
            
            window.currentActionLpjId = id;
            window.currentActionLpjData = {
                id: id,
                noSurat: noSurat || 'N/A',
                kegiatan: kegiatan || 'N/A',
                peserta: peserta || 0,
                total: total || 'Rp 0'
            };
            
            console.log('Data stored in currentActionLpjData:', window.currentActionLpjData);
            
            // Check if modal elements exist
            const modalElements = {
                noSurat: document.getElementById('action-modal-no-surat'),
                kegiatan: document.getElementById('action-modal-kegiatan'),
                peserta: document.getElementById('action-modal-peserta'),
                total: document.getElementById('action-modal-total'),
                modal: document.getElementById('actionModal')
            };
            
            console.log('Action modal elements:', modalElements);
            
            // Update modal content safely
            if (modalElements.noSurat) modalElements.noSurat.textContent = window.currentActionLpjData.noSurat;
            if (modalElements.kegiatan) modalElements.kegiatan.textContent = window.currentActionLpjData.kegiatan;
            if (modalElements.peserta) modalElements.peserta.textContent = window.currentActionLpjData.peserta;
            if (modalElements.total) modalElements.total.textContent = window.currentActionLpjData.total;
            
            // Show modal
            if (modalElements.modal) {
                modalElements.modal.classList.remove('hidden');
                console.log('Action modal shown successfully');
            } else {
                console.error('Action modal element not found');
                alert('Error: Modal tidak ditemukan. Silakan refresh halaman.');
            }
        };
    }
</script>