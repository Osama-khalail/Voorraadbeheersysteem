@extends('layouts.app')

@section('title', 'Gebruikers')
@section('page_title', 'Gebruikers')
@section('page_subtitle', 'Overzicht van gebruikers')

@section('content')
    <div class="bg-white shadow rounded-lg p-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-[var(--text-algemeen)] text-xl font-semibold">Overzicht Gebruikers</h2>
                <p class="text-sm text-neutral-600">Overzicht van gebruikers en rollen.</p>
            </div>

                <form action="{{ route('gebruikers.index') }}" method="GET" class="flex items-center space-x-2">
                    <input type="text" name="filter_naam" value="{{ request('filter_naam') }}" placeholder="Naam" class="border rounded px-2 py-1 text-sm" />
                    <input type="text" name="filter_email" value="{{ request('filter_email') }}" placeholder="Email" class="border rounded px-2 py-1 text-sm" />
                    <select name="filter_rol" class="border rounded px-2 py-1 text-sm">
                        <option value="">Alle rollen</option>
                        <option value="admin" {{ request('filter_rol') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="medewerker" {{ request('filter_rol') === 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                    </select>
                    <button class="bg-[#EA5521] text-white px-3 py-1 rounded text-sm">Filter</button>
                    <a href="{{ route('gebruikers.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Nieuw gebruiker</a>
                </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Naam</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Email</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Rol</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Aangemaakt</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($users ?? collect() as $user)
                    <tr>
                        <td class="px-3 py-2">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->email }}</td>
                        <td class="px-3 py-2">
                            <select class="change-role border rounded px-2 py-1 text-sm" data-id="{{ $user->id }}">
                                <option value="medewerker" {{ $user->role === 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="projectleider" {{ $user->role === 'projectleider' ? 'selected' : '' }}>Projectleider</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">{{ $user->created_at ? $user->created_at->format('d-m-Y') : '-' }}</td>
                        <td class="px-3 py-2 space-x-2">
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('gebruikers.destroy', $user->id) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="px-2 py-1 bg-red-600 text-white rounded text-xs" onclick="return confirm('Weet je het zeker?')">Verwijder</button></form>
                                <button type="button" class="px-2 py-1 bg-orange-600 text-white rounded text-xs reset-password" data-id="{{ $user->id }}" data-name="{{ $user->name }}">Wachtwoord resetten</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">Geen gebruikers gevonden.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->appends(request()->except('page'))->links() ?? '' }}
        </div>

        <script>
            (function(){
                function csrf() { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; }
                document.querySelectorAll('.change-role').forEach(function(sel){
                    sel.addEventListener('change', function(){
                        var id = this.dataset.id;
                        var role = this.value;
                        fetch('/gebruikers/'+id+'/role', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept':'application/json' },
                            body: JSON.stringify({ role: role })
                        }).then(r=>r.json()).then(function(d){
                            if(!d.success) alert('Kon rol niet bijwerken');
                        }).catch(function(){ alert('Netwerkfout bij rol update'); });
                    });
                });

                // Logica voor wachtwoord-reset modal
                var modalHtml = `\
                <div id="password-modal" class="fixed inset-0 z-50 hidden items-center justify-center">\
                    <div class="absolute inset-0 bg-black opacity-50"></div>\
                    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-4 z-10">\
                        <h3 class="text-lg font-semibold mb-2">Wachtwoord resetten voor <span id="password-modal-user"></span></h3>\
                        <div class="mb-2">\
                            <label class="block text-sm mb-1">Nieuw wachtwoord</label>\
                            <input id="password-modal-password" type="password" class="w-full border rounded px-2 py-1" />\
                        </div>\
                        <div class="mb-4">\
                            <label class="block text-sm mb-1">Bevestig wachtwoord</label>\
                            <input id="password-modal-confirm" type="password" class="w-full border rounded px-2 py-1" />\
                        </div>\
                        <div class="flex justify-end space-x-2">\
                            <button type="button" id="password-modal-cancel" class="px-3 py-1 rounded border">Annuleer</button>\
                            <button type="button" id="password-modal-confirm-btn" class="px-3 py-1 rounded bg-[#EA5521] text-white">Bevestig</button>\
                        </div>\
                    </div>\
                </div>`;

                if (!document.getElementById('password-modal')) {
                    var div = document.createElement('div');
                    div.innerHTML = modalHtml;
                    document.body.appendChild(div.firstElementChild);
                }

                function showPasswordModal(userId, userName){
                    var m = document.getElementById('password-modal');
                    if (!m) return;
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                    document.getElementById('password-modal-user').textContent = userName || '';
                    m.dataset.userId = userId;
                }

                function hidePasswordModal(){
                    var m = document.getElementById('password-modal');
                    if (!m) return;
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                    document.getElementById('password-modal-password').value = '';
                    document.getElementById('password-modal-confirm').value = '';
                }

                document.querySelectorAll('.reset-password').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        showPasswordModal(this.dataset.id, this.dataset.name);
                    });
                });

                document.getElementById('password-modal-cancel')?.addEventListener('click', hidePasswordModal);
                document.querySelector('#password-modal .absolute')?.addEventListener('click', hidePasswordModal);

                document.getElementById('password-modal-confirm-btn')?.addEventListener('click', function(){
                    var m = document.getElementById('password-modal');
                    var id = m?.dataset.userId;
                    var password = document.getElementById('password-modal-password')?.value || '';
                    var confirm = document.getElementById('password-modal-confirm')?.value || '';
                    if (!id) return;
                    if (password.length < 6) { alert('Wachtwoord moet minimaal 6 tekens zijn'); return; }
                    if (password !== confirm) { alert('Wachtwoorden komen niet overeen'); return; }
                    fetch('/gebruikers/'+id+'/password', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept':'application/json' },
                        body: JSON.stringify({ password: password, password_confirmation: confirm })
                    }).then(r=>r.json()).then(function(d){
                        if(d && d.success){
                            hidePasswordModal();
                            alert('Wachtwoord bijgewerkt');
                        } else {
                            alert('Kon wachtwoord niet bijwerken');
                        }
                    }).catch(function(){ alert('Netwerkfout bij wachtwoord reset'); });
                });
            })();
        </script>
    </div>
@endsection
