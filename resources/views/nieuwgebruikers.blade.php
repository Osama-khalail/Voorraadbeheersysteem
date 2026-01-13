@extends('layouts.app')

@section('title', 'Nieuw gebruiker')
@section('page_title', 'Nieuw gebruiker')
@section('page_subtitle', 'Nieuwe gebruiker aanmaken')

@section('content')
    <div class="container mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Nieuwe gebruiker</h2>

            @php $isEdit = isset($user); @endphp
            <form action="{{ $isEdit ? route('gebruikers.update', $user->id) : route('gebruikers.store') }}" method="POST">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Naam *</label>
                        <input type="text" name="name" class="w-full border rounded px-3 py-2 text-sm" placeholder="Naam" value="{{ old('name', $user->name ?? '') }}" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <input type="email" name="email" class="w-full border rounded px-3 py-2 text-sm" placeholder="email@example.com" value="{{ old('email', $user->email ?? '') }}" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Rol</label>
                        <select name="role" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="medewerker" {{ (old('role', $user->role ?? 'medewerker') === 'medewerker') ? 'selected' : '' }}>Medewerker</option>
                            <option value="admin" {{ (old('role', $user->role ?? '') === 'admin') ? 'selected' : '' }}>Admin</option>
                            <option value="projectleider" {{ (old('role', $user->role ?? '') === 'projectleider') ? 'selected' : '' }}>Projectleider</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Wachtwoord *</label>
                        <input type="password" name="password" placeholder="minimaal 6 tekens" class="w-full border rounded px-3 py-2 text-sm" {{ $isEdit ? '' : 'required' }} />
                        @if($isEdit)
                            <div class="text-xs text-neutral-500 mt-1">Laat leeg om wachtwoord te behouden</div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Bevestig wachtwoord</label>
                        <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2 text-sm" {{ $isEdit ? '' : 'required' }} />
                    </div>
                </div>

                <div class="mt-4 flex items-center space-x-3">
                    <a href="{{ route('gebruikers.index') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Annuleren</a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
