<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold mb-2 flex items-center">
                                <i class="fas fa-eye mr-3"></i>Detail Tiba Berangkat
                            </h1>
                            <p class="text-emerald-100">Informasi lengkap dokumen {{ $tibaBerangkat->no_surat }}</p>
                            <div class="mt-3 flex items-center text-sm text-emerald-200">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $tibaBerangkat->details->count() }} desa dikunjungi</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar mr-2"></i>
                                <span>{{ $tibaBerangkat->created_at->format('d F Y') }}</span>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                            <a href="{{ route('tiba-berangkats.index') }}" class="bg-white text-emerald-600 px-6 py-2 rounded-lg font-semibold hover:bg-emerald-50 transition duration-200 shadow-md">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                            <a href="{{ route('tiba-berangkats.download', $tibaBerangkat) }}" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-emerald-400 transition duration-200 shadow-md">
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Document Info -->
                    <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100 mb-8">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-file-alt mr-3 text-emerald-600"></i>Informasi Dokumen
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-hashtag mr-2"></i>Nomor Surat
                                    </label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $tibaBerangkat->no_surat }}</p>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>Jumlah Desa
                                    </label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $tibaBerangkat->details->count() }} Desa</p>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-plus mr-2"></i>Dibuat
                                    </label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $tibaBerangkat->created_at->format('d F Y H:i') }}</p>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-edit mr-2"></i>Diperbarui
                                    </label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $tibaBerangkat->updated_at->format('d F Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desa Kunjungan -->
                    <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-route mr-3 text-blue-600"></i>Detail Kunjungan Desa
                            </h3>
                            
                            <div class="space-y-6">
                                @foreach ($tibaBerangkat->details as $index => $detail)
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                                <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">
                                                    {{ $index + 1 }}
                                                </span>
                                                Kunjungan Desa {{ $index + 1 }}
                                            </h4>
                                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                                {{ $detail->tanggal_kunjungan->format('d M Y') }}
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-map-pin mr-2 text-blue-500"></i>Nama Desa
                                                </label>
                                                <p class="text-sm font-semibold text-gray-900">{{ $detail->pejabatTtd->desa }}</p>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-user mr-2 text-green-500"></i>Nama Pejabat
                                                </label>
                                                <p class="text-sm font-semibold text-gray-900">{{ $detail->pejabatTtd->nama }}</p>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-id-badge mr-2 text-purple-500"></i>Jabatan
                                                </label>
                                                <p class="text-sm font-semibold text-gray-900">{{ $detail->pejabatTtd->jabatan }}</p>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-calendar-day mr-2 text-orange-500"></i>Tanggal Kunjungan
                                                </label>
                                                <p class="text-sm font-semibold text-gray-900">{{ $detail->tanggal_kunjungan->format('d F Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100 mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-bolt mr-3 text-yellow-600"></i>Aksi Cepat
                            </h3>
                            
                            <div class="space-y-3">
                                <a href="{{ route('tiba-berangkats.edit', $tibaBerangkat) }}" 
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i>Edit Dokumen
                                </a>
                                
                                <a href="{{ route('tiba-berangkats.download', $tibaBerangkat) }}" 
                                   class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>Download .docx
                                </a>
                                
                                <form action="{{ route('tiba-berangkats.destroy', $tibaBerangkat) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                        <i class="fas fa-trash mr-2"></i>Hapus Dokumen
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-bar mr-3 text-indigo-600"></i>Ringkasan
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Total Desa</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $tibaBerangkat->details->count() }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Tanggal Mulai</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $tibaBerangkat->details->min('tanggal_kunjungan')->format('d M Y') }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Tanggal Selesai</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $tibaBerangkat->details->max('tanggal_kunjungan')->addDay()->format('d M Y') }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Durasi</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ $tibaBerangkat->details->min('tanggal_kunjungan')->diffInDays($tibaBerangkat->details->max('tanggal_kunjungan')) + 1 }} hari
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>