@extends('layouts.app')

@section('title', 'Nieuwmaterialen')
@section('page_title', 'Nieuwmaterialen')
@section('page_subtitle', 'Overzicht van materialen en voorraad en activiteiten')

@section('content')
    <div class="container mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Nieuw materiaal toevoegen</h2>

            <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
                <!-- Left: form -->
                <div class="lg:col-span-2">
                    @php $isEdit = isset($product); @endphp
                    <form action="{{ $isEdit ? route('materialen.update', $product->id) : route('materialen.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($isEdit) @method('PUT') @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Naam</label>
                                <input type="text" name="naam" class="w-full border rounded px-3 py-2 text-sm" placeholder="Naam" value="{{ old('naam', $product->naam ?? '') }}" required />
                                @error('naam') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Type</label>
                                <input type="text" name="type" class="w-full border rounded px-3 py-2 text-sm" placeholder="Type" value="{{ old('type', $product->type ?? '') }}" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Leverancier</label>
                                <input type="text" name="leverancier" class="w-full border rounded px-3 py-2 text-sm" placeholder="Leverancier" value="{{ old('leverancier', $product->leverancier ?? '') }}" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-1">Omschrijving</label>
                                <textarea name="omschrijving" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="Omschrijf het materiaal">{{ old('omschrijving', $product->omschrijving ?? '') }}</textarea>
                                @error('omschrijving') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Categorie</label>
                                <select id="categorie-select" name="categorie_id" class="w-full border rounded px-3 py-2 text-sm">
                                    <option value="">-- Kies categorie --</option>
                                    @foreach($categories ?? collect() as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('categorie_id', $product->categorie_id ?? '') == $cat->id) ? 'selected' : '' }}>{{ $cat->naam }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Huidige voorraad</label>
                                    <input type="number" name="voorraad" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('voorraad', $product->stock->aantal ?? 0) }}" min="0" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Minimale voorraad</label>
                                    <input type="number" name="minimale_voorraad" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('minimale_voorraad', $product->minimale_voorraad ?? 0) }}" min="0" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center space-x-3">
                            <a href="{{ route('materialen.index') }}" class="px-4 py-2 bg-gray-200 rounded text-sm">Annuleren</a>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Opslaan</button>
                        </div>
                    </form>

                    <!-- Categorie beheren -->
                    <div class="mt-6 border-t pt-4">
                        <h3 class="text-lg font-medium mb-2">Categorie beheren</h3>
                        <div class="flex items-center space-x-2 mb-3">
                            <input id="new-category" type="text" placeholder="Nieuwe categorie" class="border rounded px-2 py-1 text-sm w-1/2" />
                            <button id="add-category" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">+</button>
                        </div>

                        <ul id="categories-list" class="space-y-2 text-sm">
                            @foreach($categories ?? collect() as $cat)
                            <li class="flex items-center justify-between bg-gray-50 p-2 rounded" data-id="{{ $cat->id }}">
                                <span>{{ $cat->naam }}</span>
                                <button class="remove-cat px-2 py-0.5 text-red-600" data-id="{{ $cat->id }}">×</button>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Right: image upload -->
                <div>
                    <label class="block text-sm font-medium mb-1">Foto (slepen of selecteren)</label>
                    <div id="drop-area" class="border-dashed border-2 border-gray-300 rounded h-48 flex items-center justify-center text-center p-4">
                        <div>
                            <p class="text-sm text-neutral-600">Sleep hier een afbeelding naartoe of klik om te selecteren.</p>
                            <p class="text-xs text-neutral-500 mt-2">PNG, JPG of GIF (max 2MB)</p>
                            <input id="file-input" name="foto" type="file" accept="image/*" class="hidden" />
                        </div>
                    </div>
                    <div id="preview" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category add/remove with AJAX persist
        const categorieStoreUrl = '{{ route('categorieen.store') }}';
        const csrfToken = '{{ csrf_token() }}';

        document.getElementById('add-category').addEventListener('click', function(e){
            e.preventDefault();
            var input = document.getElementById('new-category');
            var val = input.value.trim();
            if(!val) return;

            fetch(categorieStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ naam: val })
            })
            .then(function(res){
                if(!res.ok) throw res;
                return res.json();
            })
            .then(function(data){
                // add to categories list UI
                var ul = document.getElementById('categories-list');
                var li = document.createElement('li');
                li.className = 'flex items-center justify-between bg-gray-50 p-2 rounded';
                li.innerHTML = '<span>'+data.naam+'</span><button class="remove-cat px-2 py-0.5 text-red-600">×</button>';
                ul.appendChild(li);

                // add to select
                var sel = document.getElementById('categorie-select');
                var opt = document.createElement('option');
                opt.value = data.id;
                opt.text = data.naam;
                opt.selected = true;
                sel.appendChild(opt);

                input.value = '';
            })
            .catch(function(err){
                console.error('Category save error', err);
                alert('Kon categorie niet opslaan');
            });
        });

        const categorieDestroyUrl = '{{ url('categorieen') }}';

        document.getElementById('categories-list').addEventListener('click', function(e){
            if(e.target && e.target.classList.contains('remove-cat')){
                var btn = e.target;
                var id = btn.dataset.id || btn.closest('li').dataset.id;
                if(!id) return;
                if(!confirm('Weet je zeker dat je deze categorie wilt verwijderen?')) return;

                fetch(categorieDestroyUrl + '/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(function(res){
                    if(res.ok) return res;
                    throw res;
                })
                .then(function(){
                    // remove list item
                    var li = btn.closest('li');
                    if(li) li.remove();

                    // remove from select if present
                    var sel = document.getElementById('categorie-select');
                    if(sel){
                        var opt = sel.querySelector('option[value="'+id+'"]');
                        if(opt) opt.remove();
                    }
                })
                .catch(function(err){
                    console.error('Category delete error', err);
                    alert('Kon categorie niet verwijderen');
                });
            }
        });

        // Drag & drop image
        var dropArea = document.getElementById('drop-area');
        var fileInput = document.getElementById('file-input');
        var preview = document.getElementById('preview');

        dropArea.addEventListener('click', function(){ fileInput.click(); });
        dropArea.addEventListener('dragover', function(e){ e.preventDefault(); dropArea.classList.add('bg-gray-50'); });
        dropArea.addEventListener('dragleave', function(e){ dropArea.classList.remove('bg-gray-50'); });
        dropArea.addEventListener('drop', function(e){
            e.preventDefault(); dropArea.classList.remove('bg-gray-50');
            var files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', function(){ handleFiles(this.files); });

        function handleFiles(files){
            if(!files || !files.length) return;
            var file = files[0];
            if(!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function(e){
                preview.innerHTML = '<img src="'+e.target.result+'" class="w-full rounded" />';
            }
            reader.readAsDataURL(file);
        }
    </script>
@endsection
