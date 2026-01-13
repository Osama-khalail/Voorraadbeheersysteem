@extends('layouts.app')

@section('title', 'Logboek')
@section('page_title', 'Logboek')
@section('page_subtitle', 'Overzicht van logboekactiviteiten')

@section('content')
    <div class="bg-white shadow rounded-lg p-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-[var(--text-algemeen)] text-xl font-semibold">Overzicht Logboek</h2>
                <p class="text-sm text-neutral-600">Recente acties en wijzigingen in het systeem.</p>
            </div>
            <form action="{{ route('logboek.index') }}" method="GET" class="flex items-center space-x-2">
                <select class="border rounded px-2 py-1 text-sm" name="filter_wie">
                    <option value="">Alle gebruikers</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @if(request('filter_wie') == $u->id) selected @endif>{{ $u->name }}</option>
                    @endforeach
                </select>

                <select class="border rounded px-2 py-1 text-sm" name="filter_product">
                    <option value="">Alle producten</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @if(request('filter_product') == $p->id) selected @endif>{{ $p->naam }}</option>
                    @endforeach
                </select>

                <select class="border rounded px-2 py-1 text-sm" name="filter_actie">
                    <option value="">Alle acties</option>
                    <option value="pakken" @if(request('filter_actie') == 'pakken') selected @endif>Pakken</option>
                    <option value="bijleggen" @if(request('filter_actie') == 'bijleggen') selected @endif>Bij leggen</option>
                    <option value="verwijderen" @if(request('filter_actie') == 'verwijderen') selected @endif>Verwijderen</option>
                </select>

                <input type="text" name="filter_omschrijving" value="{{ request('filter_omschrijving') }}" placeholder="Omschrijving" class="border rounded px-2 py-1 text-sm" />

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1 text-sm" />
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1 text-sm" />

                <button class="bg-[#EA5521] text-white px-3 py-1 rounded text-sm">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Wie</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Product</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Actie</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Aantal</th>
                           <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Opmerking</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Wanneer</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-3 py-2">{{ optional($log->user)->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ optional($log->product)->naam ?? '—' }}</td>
                            <td class="px-3 py-2">{{ ucfirst($log->wijziging_type) }}</td>
                            <td class="px-3 py-2">{{ $log->aantal }}</td>
                               <td class="px-3 py-2">{{ $log->opmerking ?? '—' }}</td>
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($log->datumtijd)->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-neutral-600">Geen resultaten</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div>
                {{ $logs->links() }}
            </div>
            <div class="text-sm text-neutral-600">Pagina {{ $logs->currentPage() }} van {{ $logs->lastPage() }}</div>
            <div></div>
        </div>
    </div>
@endsection
