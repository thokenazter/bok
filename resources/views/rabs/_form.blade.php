@php
    $defaultTypes = [
        ['type' => 'transport_darat', 'label' => 'Transport Darat', 'default_price' => 70000, 'factors' => [
            ['key' => 'orang', 'label' => 'Orang', 'value' => 1],
            ['key' => 'hari', 'label' => 'Hari', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'transport_laut', 'label' => 'Transport Laut/Seberang', 'default_price' => 70000, 'factors' => [
            ['key' => 'orang', 'label' => 'Orang', 'value' => 1],
            ['key' => 'hari', 'label' => 'Hari', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'transport_harian', 'label' => 'Transport Harian', 'default_price' => 70000, 'factors' => [
            ['key' => 'orang', 'label' => 'Orang', 'value' => 1],
            ['key' => 'hari', 'label' => 'Hari', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'uang_harian', 'label' => 'Uang Harian', 'default_price' => 150000, 'factors' => [
            ['key' => 'orang', 'label' => 'Orang', 'value' => 1],
            ['key' => 'hari', 'label' => 'Hari', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'snack', 'label' => 'Snack', 'default_price' => 24000, 'factors' => [
            ['key' => 'dos', 'label' => 'Dos', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'penggandaan', 'label' => 'Penggandaan', 'default_price' => 750, 'factors' => [
            ['key' => 'lembar', 'label' => 'Lembar', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'transport_peserta', 'label' => 'Transport Peserta', 'default_price' => 70000, 'factors' => [
            ['key' => 'peserta', 'label' => 'Peserta', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'konsumsi', 'label' => 'Konsumsi', 'default_price' => 59000, 'factors' => [
            ['key' => 'porsi', 'label' => 'Porsi', 'value' => 1],
            ['key' => 'desa', 'label' => 'Desa', 'value' => 1],
            ['key' => 'kali', 'label' => 'Kali Kegiatan', 'value' => 1],
        ]],
        ['type' => 'bahan_makanan', 'label' => 'Pembelian Bahan Makanan', 'default_price' => 750000, 'factors' => [
            ['key' => 'paket', 'label' => 'Paket', 'value' => 1],
            ['key' => 'kegiatan', 'label' => 'Kegiatan', 'value' => 1],
        ]],
        ['type' => 'lainnya', 'label' => 'Lainnya', 'factors' => []],
    ];
    // Siapkan nilai awal items agar @json sederhana dan aman
    $initialItems = old('items');
    if (!$initialItems && isset($rab)) {
        $initialItems = $rab->items->map(function($i) {
            return [
                'label' => $i->label,
                'type' => $i->type,
                'unit_price' => (float) $i->unit_price,
                'factors' => collect($i->factors ?? [])->map(function($f){
                    return [
                        'key' => $f['key'] ?? ($f['label'] ?? ''),
                        'label' => $f['label'] ?? ($f['key'] ?? ''),
                        'value' => (float) ($f['value'] ?? 0),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Komponen</label>
        @php($allComponents = isset($components) ? $components : \App\Models\Rab::components())
        <select name="komponen" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">Pilih Komponen</option>
            @foreach ($allComponents as $key => $label)
                <option value="{{ $label }}" {{ old('komponen', $rab->komponen ?? '') === $label ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('komponen')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div x-data="rabMasterSelector()" class="md:col-span-2">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rincian Menu</label>
                <select x-model="rab_menu_id" @change="syncMenuName()" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Pilih berdasarkan Komponen</option>
                    <template x-for="m in menus" :key="m.id">
                        <option :value="m.id" x-text="m.name"></option>
                    </template>
                </select>
                <input type="hidden" name="rab_menu_id" :value="rab_menu_id">
                <input type="hidden" name="rincian_menu" :value="rincian_menu">
                @error('rincian_menu')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kegiatan</label>
                <select x-model="rab_kegiatan_id" @change="syncKegiatanName()" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Pilih berdasarkan Rincian Menu</option>
                    <template x-for="k in kegiatans" :key="k.id">
                        <option :value="k.id" x-text="k.name"></option>
                    </template>
                </select>
                <input type="hidden" name="rab_kegiatan_id" :value="rab_kegiatan_id">
                <input type="hidden" name="kegiatan" :value="kegiatan">
                @error('kegiatan')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Anda tetap bisa mengedit nama hasil pemilihan di template ekspor bila diperlukan.</p>
        <script>
            function rabMasterSelector() {
                return {
                    menus: [],
                    kegiatans: [],
                    rab_menu_id: @json(old('rab_menu_id', $rab->rab_menu_id ?? '')),
                    rab_kegiatan_id: @json(old('rab_kegiatan_id', $rab->rab_kegiatan_id ?? '')),
                    rincian_menu: @json(old('rincian_menu', $rab->rincian_menu ?? '')),
                    kegiatan: @json(old('kegiatan', $rab->kegiatan ?? '')),
                    init() {
                        this.$watch('rab_menu_id', (val) => {
                            if (!val) { this.kegiatans = []; this.rab_kegiatan_id=''; this.kegiatan=''; return; }
                            fetch(`{{ route('rab-kegiatans.by-menu') }}?rab_menu_id=${val}`)
                                .then(r => r.json()).then(d => { this.kegiatans = d.data || []; });
                        });
                        this.$watch('komponenSelect', () => {});
                        // load menus by current component
                        this.loadMenus();
                        if (this.rab_menu_id) {
                            fetch(`{{ route('rab-kegiatans.by-menu') }}?rab_menu_id=${this.rab_menu_id}`)
                                .then(r => r.json()).then(d => { this.kegiatans = d.data || []; });
                        }
                    },
                    loadMenus() {
                        const select = document.querySelector('select[name="komponen"]');
                        if (!select) return;
                        const selectedText = select.value; // label
                        // map label->key on server side via dataset embedded below
                        const mapping = @json(array_flip(\App\Models\Rab::components()));
                        const key = mapping[selectedText] || '';
                        if (!key) { this.menus = []; this.rab_menu_id=''; this.kegiatans=[]; this.rab_kegiatan_id=''; return; }
                        fetch(`{{ route('rab-menus.by-component') }}?component_key=${key}`)
                            .then(r => r.json()).then(d => { this.menus = d.data || []; });
                    },
                    syncMenuName() {
                        const m = this.menus.find(x => String(x.id) === String(this.rab_menu_id));
                        this.rincian_menu = m ? m.name : '';
                    },
                    syncKegiatanName() {
                        const k = this.kegiatans.find(x => String(x.id) === String(this.rab_kegiatan_id));
                        this.kegiatan = k ? k.name : '';
                    }
                }
            }
            // observe komponen change globally to reload menus
            document.addEventListener('alpine:init', () => {
                document.querySelector('select[name="komponen"]').addEventListener('change', () => {
                    const comp = Alpine.$data(document.querySelector('[x-data^=rabMasterSelector]'));
                    if (comp) { comp.loadMenus(); comp.rab_menu_id=''; comp.kegiatans=[]; comp.rab_kegiatan_id=''; comp.rincian_menu=''; comp.kegiatan=''; }
                });
            });
        </script>
    </div>
</div>

<div class="mt-8" x-data="rabBuilder()">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900">Item Kegiatan</h3>
        <div class="flex gap-2">
            <select x-model="newItemType" class="rounded-lg border-gray-300">
                <option value="">Pilih Tipe Item</option>
                @foreach ($defaultTypes as $t)
                    <option value='@json($t)'>{{ $t['label'] }}</option>
                @endforeach
            </select>
            <button type="button" @click="addPresetItem()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Tambah</button>
            <button type="button" @click="addBlankItem()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Item Kosong</button>
        </div>
    </div>

    <template x-if="items.length === 0">
        <div class="text-gray-500 border border-dashed rounded-lg p-6">Belum ada item. Tambahkan item terlebih dahulu.</div>
    </template>

    <div class="space-y-4">
        <template x-for="(item, idx) in items" :key="idx">
            <div class="border rounded-xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Item</label>
                        <input class="w-full rounded-lg border-gray-300" type="text" :name="`items[${idx}][label]`" x-model="item.label" placeholder="cth: Transport Darat" required>
                        <input type="hidden" :name="`items[${idx}][type]`" x-model="item.type">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Satuan (Rp)</label>
                        <input class="w-full rounded-lg border-gray-300" type="number" step="0.01" min="0" :name="`items[${idx}][unit_price]`" x-model.number="item.unit_price" required>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sub Total (auto)</label>
                        <div class="w-full px-3 py-2 bg-gray-50 rounded-lg border border-gray-200" x-text="formatRupiah(subtotal(item))"></div>
                    </div>
                    <div class="md:col-span-1 flex md:justify-end gap-2">
                        <button type="button" @click="removeItem(idx)" class="bg-red-50 text-red-700 hover:bg-red-100 px-3 py-2 rounded-lg">Hapus</button>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-800">Faktor Perhitungan</h4>
                        <button type="button" @click="addFactor(idx)" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded">Tambah Faktor</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3" >
                        <template x-for="(f, fi) in item.factors" :key="fi">
                            <div class="grid grid-cols-12 gap-2 items-end">
                                <div class="col-span-5">
                                    <label class="block text-xs text-gray-600 mb-1">Nama Faktor</label>
                                    <input class="w-full rounded-lg border-gray-300" type="text" :name="`items[${idx}][factors][${fi}][label]`" x-model="f.label" placeholder="cth: Orang">
                                    <input type="hidden" :name="`items[${idx}][factors][${fi}][key]`" x-model="f.key">
                                </div>
                                <div class="col-span-5">
                                    <label class="block text-xs text-gray-600 mb-1">Nilai</label>
                                    <input class="w-full rounded-lg border-gray-300" type="number" step="0.01" min="0" :name="`items[${idx}][factors][${fi}][value]`" x-model.number="f.value">
                                </div>
                                <div class="col-span-2 flex justify-end">
                                    <button type="button" @click="removeFactor(idx, fi)" class="text-sm text-red-600 hover:underline">Hapus</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <div class="text-lg font-semibold">Jumlah Total: <span x-text="formatRupiah(grandTotal())"></span></div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl">Simpan RAB</button>
    </div>

    <script>
        function rabBuilder() {
            return {
                items: @json($initialItems ?? []),
                newItemType: '',
                addPresetItem() {
                    if (!this.newItemType) return;
                    const preset = JSON.parse(this.newItemType);
                    this.items.push({
                        label: preset.label,
                        type: preset.type,
                        unit_price: preset.default_price || 0,
                        factors: (preset.factors || []).map(f => ({...f}))
                    });
                    this.newItemType = '';
                },
                addBlankItem() {
                    this.items.push({ label: 'Item', type: 'lainnya', unit_price: 0, factors: [] });
                },
                removeItem(idx) { this.items.splice(idx, 1); },
                addFactor(idx) {
                    this.items[idx].factors.push({ key: 'f' + Date.now(), label: 'Faktor', value: 1 });
                },
                removeFactor(idx, fi) { this.items[idx].factors.splice(fi, 1); },
                subtotal(item) {
                    const qty = (item.factors || []).reduce((acc, f) => acc * (parseFloat(f.value || 0) || 0), 1);
                    return qty * (parseFloat(item.unit_price || 0) || 0);
                },
                grandTotal() { return this.items.reduce((s, it) => s + this.subtotal(it), 0); },
                formatRupiah(n) {
                    try {
                        return 'Rp ' + (n||0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    } catch (e) { return 'Rp ' + Math.round(n||0).toString(); }
                }
            }
        }
    </script>
</div>
