<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex items-start md:items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold mb-1">RAB BOK Puskesmas</h1>
                            <p class="text-indigo-100">Kelola Rencana Anggaran Biaya kegiatan</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            @if(auth()->user()?->isSuperAdmin())
                                <a href="{{ route('rabs.create') }}" class="inline-flex items-center bg-white text-indigo-700 px-4 py-2 rounded-lg font-medium hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                    <i class="fas fa-plus mr-2"></i>Buat RAB
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100 mb-8">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Komponen</label>
                            <select name="komponen" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Komponen</option>
                                @foreach ($componentsList as $c)
                                    <option value="{{ $c }}" {{ ($selectedKomponen ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rincian Menu</label>
                            <select name="menu" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Rincian Menu</option>
                                @foreach ($menuList as $m)
                                    <option value="{{ $m }}" {{ ($selectedMenu ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kegiatan</label>
                            <select name="kegiatan" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Kegiatan</option>
                                @foreach ($kegiatanList as $k)
                                    <option value="{{ $k }}" {{ ($selectedKegiatan ?? '') === $k ? 'selected' : '' }}>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="h-10 mt-6 inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 rounded-xl"><i class="fas fa-filter mr-2"></i>Terapkan</button>
                            <a href="{{ route('rabs.index') }}" class="h-10 mt-6 inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 rounded-xl"><i class="fas fa-rotate-left mr-2"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-1 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Distribusi per Komponen</h3>
                    </div>
                    <div class="h-64">
                        <canvas id="rabByComponentChart" class="w-full h-full"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Top 10 Rincian Menu (Total RAB)</h3>
                    </div>
                    <div class="h-64">
                        <canvas id="rabByMenuChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-2xl border border-gray-100">
                <div class="p-6">
                    @if (session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Komponen</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rincian Menu</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kegiatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($rabs as $rab)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $rab->komponen }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $rab->rincian_menu }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $rab->kegiatan }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">Rp {{ number_format($rab->total, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('rabs.show', $rab) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-eye mr-1"></i>Lihat</a>
                                            @if(auth()->user()?->isSuperAdmin())
                                                <a href="{{ route('rabs.edit', $rab) }}" class="inline-flex items-center text-yellow-600 hover:text-yellow-900 mr-3"><i class="fas fa-edit mr-1"></i>Edit</a>
                                                <a href="{{ route('rabs.export', $rab) }}" class="inline-flex items-center text-green-600 hover:text-green-800 mr-3"><i class="fas fa-file-excel mr-1"></i>Export</a>
                                                <form action="{{ route('rabs.destroy', $rab) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-800"><i class="fas fa-trash mr-1"></i>Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-6 text-center text-gray-500">Belum ada RAB</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $rabs->links() }}</div>
                </div>
            </div>

            <div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Top 10 Kegiatan (Total RAB)</h3>
                </div>
                <div class="h-64">
                    <canvas id="rabByKegiatanChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Prepare datasets from server
        const byComponent = @json(($byComponent ?? collect())->map(fn($i) => ['label' => $i->komponen, 'total' => (float)$i->total]));
        const byMenu = @json(($byMenu ?? collect())->map(fn($i) => ['label' => $i->rincian_menu, 'total' => (float)$i->total]));
        const byKegiatan = @json(($byKegiatan ?? collect())->map(fn($i) => ['label' => $i->kegiatan, 'total' => (float)$i->total]));

        // Utility palette + fixed colors per Komponen
        const palette = [
            '#4F46E5','#9333EA','#22C55E','#F59E0B','#EF4444','#06B6D4','#8B5CF6','#10B981','#F97316','#3B82F6',
        ];
        const componentColors = {
            'Peningkatan Layanan Kesehatan Sesuai Siklus Hidup': '#EC4899', // pink-500
            'Surveilans, respons penyakit dan kesehatan lingkungan': '#06B6D4', // cyan-500
            'Pemberian Makanan Tambahan (PMT) berbahan pangan lokal': '#F97316', // orange-500
            'MANAGEMEN PUSKESMAS': '#6366F1', // indigo-500
            'INSENTIF UKM': '#10B981', // emerald-500
        };

        // Doughnut for components
        (function(){
            const el = document.getElementById('rabByComponentChart'); if (!el) return;
            const labels = byComponent.map(i => i.label);
            const data = byComponent.map(i => i.total);
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: labels.map((label, idx) => componentColors[label] ?? palette[idx % palette.length]),
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        })();

        // Horizontal bar for menus
        (function(){
            const el = document.getElementById('rabByMenuChart'); if (!el) return;
            const labels = byMenu.map(i => i.label);
            const data = byMenu.map(i => i.total);
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total (Rp)',
                        data,
                        backgroundColor: 'rgba(79,70,229,0.2)',
                        borderColor: '#4F46E5',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
                    }
                }
            });
        })();

        // Bar for kegiatan
        (function(){
            const el = document.getElementById('rabByKegiatanChart'); if (!el) return;
            const labels = byKegiatan.map(i => i.label);
            const data = byKegiatan.map(i => i.total);
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total (Rp)',
                        data,
                        backgroundColor: 'rgba(16,185,129,0.2)',
                        borderColor: '#10B981',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true },
                        x: { ticks: { callback: (v, i) => labels[i] } }
                    }
                }
            });
        })();
    </script>
</x-app-layout>
