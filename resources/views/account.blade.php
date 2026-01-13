@extends('layouts.app')

@section('title', 'Account')
@section('page_title', 'Account')
@section('page_subtitle', 'Overzicht van materialen en voorraad en activiteiten')

@section('content')
    <div class="container mx-auto">
        <div class="bg-white shadow rounded-lg p-6 max-w-3xl mx-auto">
            <h2 class="text-xl font-semibold mb-4">Mijn account </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @if(session('success'))
                            <div class="mb-3 text-green-600">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="mb-3 text-red-600">
                                <ul class="list-disc pl-5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Naam</label>
                            <input type="text" name="name" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('name', auth()->user()->name) }}" required />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('email', auth()->user()->email) }}" required />
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h3 class="text-lg font-medium mb-2">Wachtwoord wijzigen</h3>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Huidig wachtwoord</label>
                                <input type="password" name="current_password" class="w-full border rounded px-3 py-2 text-sm" />
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Nieuw wachtwoord</label>
                                <input type="password" name="new_password" class="w-full border rounded px-3 py-2 text-sm" />
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">Bevestig nieuw wachtwoord</label>
                                <input type="password" name="new_password_confirmation" class="w-full border rounded px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <div class="mt-4 flex items-center space-x-3">
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Annuleren</a>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Opslaan</button>
                        </div>
                    </form>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Profielfoto</label>
                    <div id="avatar-drop" class="border-dashed border-2 border-gray-300 rounded h-40 flex items-center justify-center text-center p-3">
                        <div>
                            <p class="text-sm text-neutral-600">Sleep hier je profielfoto of klik om te selecteren.</p>
                            <input id="avatar-input" type="file" accept="image/*" class="hidden" />
                        </div>
                    </div>
                    <div id="avatar-preview" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Avatar drag & drop preview
        var drop = document.getElementById('avatar-drop');
        var input = document.getElementById('avatar-input');
        var preview = document.getElementById('avatar-preview');
        drop.addEventListener('click', function(){ input.click(); });
        drop.addEventListener('dragover', function(e){ e.preventDefault(); drop.classList.add('bg-gray-50'); });
        drop.addEventListener('dragleave', function(e){ drop.classList.remove('bg-gray-50'); });
        drop.addEventListener('drop', function(e){ e.preventDefault(); drop.classList.remove('bg-gray-50'); handleAvatar(e.dataTransfer.files); });
        input.addEventListener('change', function(){ handleAvatar(this.files); });
        function handleAvatar(files){ if(!files || !files.length) return; var f = files[0]; if(!f.type.startsWith('image/')) return; var r = new FileReader(); r.onload = function(e){ preview.innerHTML = '<img src="'+e.target.result+'" class="w-32 h-32 object-cover rounded-full" />'; }; r.readAsDataURL(f); }
    </script>
@endsection
