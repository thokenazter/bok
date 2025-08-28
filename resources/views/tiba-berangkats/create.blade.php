<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl shadow-xl p-6 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold mb-2 flex items-center">
                                <i class="fas fa-plus-circle mr-3"></i>Buat Tiba Berangkat Baru
                            </h1>
                            <p class="text-emerald-100">Buat dokumen tiba berangkat untuk kunjungan desa</p>
                            <div class="mt-3 flex items-center text-sm text-emerald-200">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span>Isi form dengan lengkap untuk generate dokumen</span>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="{{ route('tiba-berangkats.index') }}" class="bg-white text-emerald-600 px-6 py-2 rounded-lg font-semibold hover:bg-emerald-50 transition duration-200 shadow-md">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">
                <div class="p-8">
                    <form action="{{ route('tiba-berangkats.store') }}" method="POST" x-data="tibaBerangkatForm()">
                        @csrf

                        <!-- Nomor Surat Section -->
                        <div class="mb-8">
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-file-alt mr-3 text-emerald-600"></i>Informasi Dokumen
                                </h3>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="no_surat" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-hashtag mr-2"></i>Nomor Surat
                                        </label>
                                        <input type="text" 
                                               name="no_surat" 
                                               id="no_surat" 
                                               value="{{ old('no_surat') }}" 
                                               placeholder="contoh : 666 - lihat RPK"
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition duration-150 @error('no_surat') border-red-300 @enderror">
                                        @error('no_surat')
                                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desa Kunjungan Section -->
                        <div class="mb-8">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-3 text-blue-600"></i>Desa Kunjungan
                                    </h3>
                                    <button type="button" 
                                            @click="addDesa()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md">
                                        <i class="fas fa-plus mr-2"></i>Tambah Desa
                                    </button>
                                </div>

                                <div id="desa-container" class="space-y-6">
                                    <template x-for="(desa, index) in desas" :key="index">
                                        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                            <div class="flex justify-between items-center mb-4">
                                                <h4 class="text-lg font-medium text-gray-900 flex items-center" x-text="`Desa ${index + 1}`">
                                                    <i class="fas fa-map-pin mr-2 text-blue-500"></i>
                                                </h4>
                                                <button type="button" 
                                                        @click="removeDesa(index)" 
                                                        x-show="desas.length > 1" 
                                                        class="text-red-600 hover:text-red-800 font-medium text-sm transition duration-150">
                                                    <i class="fas fa-trash mr-1"></i>Hapus
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-map-marker-alt mr-2"></i>Pilih Desa
                                                    </label>
                                                    <select :name="`desa[${index}][pejabat_ttd_id]`" 
                                                            x-model="desa.pejabat_ttd_id"
                                                            @change="updatePejabat(index, $event.target.value)"
                                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                                                        <option value="">Pilih Desa</option>
                                                        @foreach ($pejabatTtds as $pejabat)
                                                            <option value="{{ $pejabat->id }}" 
                                                                    data-nama="{{ $pejabat->nama }}" 
                                                                    data-jabatan="{{ $pejabat->jabatan }}"
                                                                    data-desa="{{ $pejabat->desa }}">
                                                                {{ $pejabat->desa }} - {{ $pejabat->nama }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-calendar-alt mr-2"></i>Tanggal Kunjungan
                                                    </label>
                                                    <input type="date" 
                                                           :name="`desa[${index}][tanggal_kunjungan]`" 
                                                           x-model="desa.tanggal_kunjungan"
                                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-150">
                                                </div>
                                            </div>

                                            <!-- Pejabat Info -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6" x-show="desa.pejabat_ttd_id">
                                                <div class="bg-gray-50 rounded-lg p-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        <i class="fas fa-user mr-2"></i>Nama Pejabat
                                                    </label>
                                                    <p class="text-sm text-gray-900 font-medium" x-text="desa.nama_pejabat"></p>
                                                </div>
                                                <div class="bg-gray-50 rounded-lg p-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        <i class="fas fa-id-badge mr-2"></i>Jabatan
                                                    </label>
                                                    <p class="text-sm text-gray-900 font-medium" x-text="desa.jabatan"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                @error('desa')
                                    <p class="mt-4 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('tiba-berangkats.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                            <button type="submit" 
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md">
                                <i class="fas fa-save mr-2"></i>Simpan Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tibaBerangkatForm() {
            return {
                desas: [
                    {
                        pejabat_ttd_id: '',
                        tanggal_kunjungan: '',
                        nama_pejabat: '',
                        jabatan: '',
                        nama_desa: ''
                    }
                ],

                addDesa() {
                    this.desas.push({
                        pejabat_ttd_id: '',
                        tanggal_kunjungan: '',
                        nama_pejabat: '',
                        jabatan: '',
                        nama_desa: ''
                    });
                },

                removeDesa(index) {
                    if (this.desas.length > 1) {
                        this.desas.splice(index, 1);
                    }
                },

                updatePejabat(index, pejabatId) {
                    if (pejabatId) {
                        const option = document.querySelector(`option[value="${pejabatId}"]`);
                        if (option) {
                            this.desas[index].nama_pejabat = option.dataset.nama;
                            this.desas[index].jabatan = option.dataset.jabatan;
                            this.desas[index].nama_desa = option.dataset.desa;
                        }
                    } else {
                        this.desas[index].nama_pejabat = '';
                        this.desas[index].jabatan = '';
                        this.desas[index].nama_desa = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>