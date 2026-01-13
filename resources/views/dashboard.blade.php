@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overzicht van materialen en voorraad en activiteiten')

@section('content')
    <div class="container mx-auto">
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
            <!-- Left: large box with table (Overzicht materialen) -->
            <div class="lg:col-span-2 bg-white shadow rounded-lg p-4">
                <h2 class="text-[var(--text-algemeen)] text-xl font-semibold mb-4">Overzicht materialen</h2>

                    @if(in_array(auth()->user()->role ?? '', ['admin','projectleider']))
                    <form id="dashboard-bulk-form" action="{{ route('materialen.bulk') }}" method="POST">
                        @csrf
                        <input type="hidden" name="return_to" value="dashboard" />
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700"><input id="select-all" type="checkbox" /></th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Leverancier</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Omschrijving</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Categorie</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Voorraad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($products ?? collect() as $product)
                            <tr>
                                <td class="px-4 py-2"><input type="checkbox" name="selected[]" value="{{ $product->id }}" /></td>
                                <td class="px-4 py-2">{{ $product->type ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $product->leverancier ?? '-' }}</td>
                                <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($product->omschrijving, 60) }}</td>
                                <td class="px-4 py-2">{{ $product->category->naam ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $product->stock->aantal ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">Geen producten gevonden.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-start space-x-3">
                        <label for="bulk-action" class="sr-only">Selecteer actie</label>
                        <select id="bulk-action" name="action" class="border rounded px-3 py-2 text-sm">
                            <option value="">Selecteer actie</option>
                            <option value="pakken">Pakken </option>
                            <option value="bijleggen">Bij leggen </option>
                            <option value="verwijderen">Verwijderen</option>
                        </select>
                        <button type="submit" class="bg-[#EA5521] text-white px-4 py-2 rounded text-sm">Uitvoeren</button>
                    </div>
                    </form>

                <!--  action modal -->
                <div id="bulk-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
                    <div class="absolute inset-0 bg-black opacity-50"></div>
                    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-4 z-10">
                        <h3 class="text-lg font-semibold mb-2">Actie: <span id="bulk-modal-action"></span></h3>
                        <div class="mb-2">
                            <label class="block text-sm mb-1">Aantal</label>
                            <input id="bulk-modal-amount" type="number" min="1" value="1" class="w-full border rounded px-2 py-1" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm mb-1">Opmerking (optioneel)</label>
                            <input id="bulk-modal-reason" type="text" class="w-full border rounded px-2 py-1" />
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" id="bulk-modal-cancel" class="px-3 py-1 rounded border">Annuleer</button>
                            <button type="button" id="bulk-modal-confirm" class="px-3 py-1 rounded bg-[#EA5521] text-white">Bevestig</button>
                        </div>
                    </div>
                </div>

                <script>
                    (function(){
                        const form = document.getElementById('dashboard-bulk-form');
                        const selectAll = document.getElementById('select-all');
                        const modal = document.getElementById('bulk-modal');
                        const modalAction = document.getElementById('bulk-modal-action');
                        const modalAmount = document.getElementById('bulk-modal-amount');
                        const modalReason = document.getElementById('bulk-modal-reason');
                        const modalCancel = document.getElementById('bulk-modal-cancel');
                        const modalConfirm = document.getElementById('bulk-modal-confirm');

                        if (selectAll) {
                            selectAll.addEventListener('change', function(){
                                const checked = this.checked;
                                form.querySelectorAll('input[name="selected[]"]').forEach(function(cb){ cb.checked = checked; });
                            });
                        }

                        function showModal(action) {
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

                        // remove previous hidden inputs
                        function removeHiddenInputs() {
                            ['bulk_amount','bulk_reason'].forEach(function(name){
                                const el = form.querySelector('input[name="'+name+'"]');
                                if (el) el.remove();
                            });
                        }

                        form.addEventListener('submit', function(e){
                            const action = document.getElementById('bulk-action').value;
                            const checked = form.querySelectorAll('input[name="selected[]"]:checked');
                            if (!action) {
                                e.preventDefault();
                                alert('Selecteer een actie.');
                                return;
                            }
                            if (checked.length === 0) {
                                e.preventDefault();
                                alert('Selecteer eerst één of meer producten.');
                                return;
                            }

                            removeHiddenInputs();

                            if (action === 'verwijderen') {
                                if (!confirm('Weet je zeker dat je de geselecteerde producten wilt verwijderen?')) {
                                    e.preventDefault();
                                    return;
                                }
                                return; // allow submit
                            }

                            if (action === 'pakken' || action === 'bijleggen') {
                                e.preventDefault();
                                showModal(action);
                                // confirm handler will add inputs and submit
                                return;
                            }

                            // Unknown action
                            e.preventDefault();
                            alert('Onbekende actie geselecteerd.');
                            return;
                        });

                        modalCancel.addEventListener('click', function(){ hideModal(); });
                        // click outside modal to close
                        modal.addEventListener('click', function(e){ if (e.target === modal) hideModal(); });

                        modalConfirm.addEventListener('click', function(){
                            const qty = parseInt(modalAmount.value, 10);
                            if (isNaN(qty) || qty <= 0) { alert('Ongeldig aantal'); return; }
                            removeHiddenInputs();
                            const input = document.createElement('input');
                            input.type = 'hidden'; input.name = 'bulk_amount'; input.value = qty; form.appendChild(input);
                            const reason = modalReason.value.trim();
                            if (reason !== '') {
                                const r = document.createElement('input');
                                r.type = 'hidden'; r.name = 'bulk_reason'; r.value = reason; form.appendChild(r);
                            }
                            hideModal();
                            form.submit();
                        });
                    })();
                </script>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Leverancier</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Omschrijving</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Categorie</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Voorraad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($products ?? collect() as $product)
                            <tr>
                                <td class="px-4 py-2">{{ $product->type ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $product->leverancier ?? '-' }}</td>
                                <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($product->omschrijving, 60) }}</td>
                                <td class="px-4 py-2">{{ $product->category->naam ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $product->stock->aantal ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">Geen producten gevonden.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                    @endif
            </div>

            <!-- Right column: two stacked boxes -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-4">
                    <h2 class="text-[var(--text-algemeen)] text-lg font-semibold mb-2">Lage voorraad</h2>
                    <p class="text-sm text-neutral-600">Items met voorraad onder de minimumwaarde.</p>

                        <div class="overflow-x-auto mt-3">
                            <table class="min-w-full text-sm text-gray-700">
                                <thead>
                                    <tr class="text-left">
                                        <th class="px-2 py-1 font-medium">Type</th>
                                        <th class="px-2 py-1 font-medium">Huidige voorraad</th>
                                        <th class="px-2 py-1 font-medium">Minimum</th>
                                    </tr>
                                </thead>
                                        <tbody class="divide-y">
                                            @forelse($lowStock ?? collect() as $p)
                                            <tr>
                                                <td class="px-2 py-1">{{ $p->type ?? $p->naam ?? '-' }}</td>
                                                <td class="px-2 py-1">{{ optional($p->stock)->aantal ?? 0 }}</td>
                                                <td class="px-2 py-1">{{ $p->minimale_voorraad ?? 0 }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="px-2 py-1 text-sm text-gray-500">Geen lage of lege voorraden</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                            </table>
                        </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <h2 class="text-[var(--text-algemeen)] text-lg font-semibold mb-2">Logboek</h2>
                    <p class="text-sm text-neutral-600">Recente activiteiten en wijzigingen worden hier getoond.</p>
                        <div class="overflow-x-auto mt-3">
                            <table class="min-w-full text-sm text-gray-700">
                                <thead>
                                    <tr class="text-left">
                                        <th class="px-2 py-1 font-medium">Wie</th>
                                        <th class="px-2 py-1 font-medium">Product</th>
                                        <th class="px-2 py-1 font-medium">Actie</th>
                                        <th class="px-2 py-1 font-medium">Aantal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($logs ?? collect() as $log)
                                        <tr>
                                            <td class="px-2 py-1">{{ optional($log->user)->name ?? '—' }}</td>
                                            <td class="px-2 py-1">{{ optional($log->product)->naam ?? '—' }}</td>
                                            <td class="px-2 py-1">{{ ucfirst($log->wijziging_type) }}</td>
                                            <td class="px-2 py-1">{{ $log->aantal }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-2 py-1 text-sm text-gray-500">Geen recente activiteiten</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
