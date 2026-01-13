@extends('layouts.app')

@section('title', 'Materialen')
@section('page_title', 'Materialen')
@section('page_subtitle', 'Overzicht van materialen en voorraad')

@section('content')
    <div class="bg-white shadow rounded-lg p-4">
            <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div>
                    <h2 class="text-[var(--text-algemeen)] text-xl font-semibold">Overzicht materialen</h2>
                    <p class="text-sm text-neutral-600">Lijst van materialen met voorraad- en minimumwaarden.</p>
                </div>
                @if(in_array(auth()->user()->role ?? '', ['admin','projectleider']))
                    <a href="{{ route('materialen.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Nieuw materiaal</a>
                @endif
            </div>

            <form action="{{ route('materialen.index') }}" method="GET" class="flex items-center space-x-2">
                <select class="border rounded px-2 py-1 text-sm" name="filter_type">
                    <option value="">Alle types</option>
                    @foreach(($types ?? collect()) as $t)
                        <option value="{{ $t }}" {{ request('filter_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <select class="border rounded px-2 py-1 text-sm" name="filter_categorie">
                    <option value="">Alle categorieën</option>
                    <option value="onder_min" {{ request('filter_categorie') === 'onder_min' ? 'selected' : '' }}>Onder minimum</option>
                    <option value="boven_min" {{ request('filter_categorie') === 'boven_min' ? 'selected' : '' }}>Boven minimum</option>
                    @foreach(($categories ?? collect()) as $cat)
                        <option value="{{ $cat->id }}" {{ (string)request('filter_categorie') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->naam }}</option>
                    @endforeach
                </select>
                <select class="border rounded px-2 py-1 text-sm" name="filter_leverancier">
                    <option value="">Alle leveranciers</option>
                    @foreach(($leveranciers ?? collect()) as $l)
                        <option value="{{ $l }}" {{ request('filter_leverancier') == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Zoeken in type, leverancier, omschrijving, categorie, voorraad, min. voorraad" class="border rounded px-2 py-1 text-sm w-80" />
                <button class="bg-[#EA5521] text-white px-3 py-1 rounded text-sm">Filter</button>
                <a href="{{ route('materialen.index') }}" class="px-3 py-1 border rounded text-sm">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700"></th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Foto</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Leverancier</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Omschrijving</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Categorie</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Voorraad</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Minimale voorraad</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($products ?? collect() as $product)
                    <tr>
                        <td class="px-3 py-2"><input type="checkbox" name="selected[]" value="{{ $product->id }}" /></td>
                        <td class="px-3 py-2">
                            @if($product->foto_url)
                                <img src="{{ $product->foto_url }}" alt="foto" class="w-12 h-8 object-cover rounded" />
                            @else
                                <div class="w-12 h-8 bg-gray-100 rounded flex items-center justify-center text-xs text-gray-400">N/A</div>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $product->type ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $product->leverancier ?? '-' }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($product->omschrijving, 60) }}</td>
                        <td class="px-3 py-2">{{ $product->category->naam ?? '-' }}</td>
                        <td class="px-3 py-2 stock-count" data-product-id="{{ $product->id }}">{{ $product->stock->aantal ?? 0 }}</td>
                        <td class="px-3 py-2">{{ $product->minimale_voorraad }}</td>
                        <td class="px-3 py-2 space-x-1">
                            @if(in_array(auth()->user()->role ?? '', ['admin','projectleider']))
                                <a href="{{ route('materialen.edit', $product->id) }}" class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Bewerk</a>
                                <button class="px-2 py-1 bg-orange-500 text-white rounded text-xs pakken-btn" data-id="{{ $product->id }}">Pakken</button>
                                <button class="px-2 py-1 bg-green-600 text-white rounded text-xs bijleggen-btn" data-id="{{ $product->id }}">Bijleggen</button>
                            @endif
                            @if((auth()->user()->role ?? '') === 'admin')
                                <form action="{{ route('materialen.destroy', $product->id) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="px-2 py-1 bg-red-600 text-white rounded text-xs" onclick="return confirm('Weet je het zeker?')">Verwijderen</button></form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">Geen producten gevonden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->appends(request()->except('page'))->links() ?? '' }}
        </div>
    </div>
        <!-- Action modal for individuele pakken/bijleggen -->
        <div id="action-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div class="absolute inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-4 z-10">
                <h3 class="text-lg font-semibold mb-2">Actie: <span id="action-modal-action"></span></h3>
                <div class="mb-2">
                    <label class="block text-sm mb-1">Aantal</label>
                    <input id="action-modal-amount" type="number" min="1" value="1" class="w-full border rounded px-2 py-1" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm mb-1">Opmerking (optioneel)</label>
                    <input id="action-modal-reason" type="text" class="w-full border rounded px-2 py-1" />
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="action-modal-cancel" class="px-3 py-1 rounded border">Annuleer</button>
                    <button type="button" id="action-modal-confirm" class="px-3 py-1 rounded bg-[#EA5521] text-white">Bevestig</button>
                </div>
            </div>
        </div>

        <script>
            (function(){
                function csrf() {
                    const m = document.querySelector('meta[name="csrf-token"]');
                    return m ? m.getAttribute('content') : '';
                }

                async function postAction(url, payload) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        body: JSON.stringify(payload||{})
                    });
                    return res.json().then(d => ({ok: res.ok, data: d}));
                }

                const modal = document.getElementById('action-modal');
                const modalAction = document.getElementById('action-modal-action');
                const modalAmount = document.getElementById('action-modal-amount');
                const modalReason = document.getElementById('action-modal-reason');
                const modalCancel = document.getElementById('action-modal-cancel');
                const modalConfirm = document.getElementById('action-modal-confirm');

                let currentProductId = null;
                let currentAction = null; // 'pakken' or 'bijleggen'

                function showModal(action, productId) {
                    currentProductId = productId;
                    currentAction = action;
                    modalAction.textContent = action === 'pakken' ? 'Pakken' : 'Bij leggen';
                    modalAmount.value = 1;
                    modalReason.value = '';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function hideModal() {
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                }

                document.addEventListener('click', function(e){
                    const pakBtn = e.target.closest('.pakken-btn');
                    const bijBtn = e.target.closest('.bijleggen-btn');
                    if (pakBtn) {
                        const id = pakBtn.getAttribute('data-id');
                        showModal('pakken', id);
                    }
                    if (bijBtn) {
                        const id = bijBtn.getAttribute('data-id');
                        showModal('bijleggen', id);
                    }
                });

                modalCancel.addEventListener('click', hideModal);
                modal.addEventListener('click', function(e){ if (e.target === modal) hideModal(); });

                modalConfirm.addEventListener('click', function(){
                    const qty = parseInt(modalAmount.value, 10);
                    if (isNaN(qty) || qty <= 0) { alert('Ongeldig aantal'); return; }
                    const reason = modalReason.value.trim();
                    const id = currentProductId;
                    const action = currentAction;
                    if (!id || !action) { hideModal(); return; }
                    postAction(`/materialen/${id}/` + action, {aantal: qty, reden: reason}).then(r=>{
                        if (!r.ok) { alert(r.data.error || 'Fout bij actie'); return; }
                        const td = document.querySelector('.stock-count[data-product-id="'+id+'"]');
                        if (td) td.textContent = r.data.newAmount;
                        hideModal();
                    }).catch(()=>{ alert('Netwerkfout'); });
                });
            })();
        </script>
@endsection
