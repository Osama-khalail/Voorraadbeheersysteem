<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category','stock'])->orderBy('naam');

        // Vrije zoekopdracht over meerdere velden
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function($q) use ($term) {
                $q->where('naam', 'like', "%{$term}%")
                  ->orWhere('type', 'like', "%{$term}%")
                  ->orWhere('leverancier', 'like', "%{$term}%")
                  ->orWhere('omschrijving', 'like', "%{$term}%");

                // Zoek in categorie-naam
                $q->orWhereHas('category', function($cq) use ($term) {
                    $cq->where('naam', 'like', "%{$term}%");
                });

                // Als numeriek: match voorraad en minimale_voorraad op exact aantal
                if (is_numeric($term)) {
                    $num = (int) $term;
                    $q->orWhere('minimale_voorraad', '=', $num)
                      ->orWhereHas('stock', function($sq) use ($num) {
                          $sq->where('aantal', '=', $num);
                      });
                }
            });
        }

        if ($request->filled('filter_type')) {
            $query->where('type', $request->input('filter_type'));
        }
        if ($request->filled('filter_categorie')) {
            $catFilter = $request->input('filter_categorie');
            if (is_numeric($catFilter)) {
                $query->where('categorie_id', $catFilter);
            } elseif ($catFilter === 'onder_min') {
                $query->whereHas('stock', function($sq) {
                    $sq->whereColumn('aantal', '<', 'products.minimale_voorraad');
                });
            } elseif ($catFilter === 'boven_min') {
                $query->whereHas('stock', function($sq) {
                    $sq->whereColumn('aantal', '>', 'products.minimale_voorraad');
                });
            }
        }
        if ($request->filled('filter_leverancier')) {
            $query->where('leverancier', $request->input('filter_leverancier'));
        }
        // Oude enkele-veld zoekfilter op omschrijving vervangen door algemene 'q'

        $products = $query->paginate(15);

        // lists for filter dropdowns
        $types = Product::select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $leveranciers = Product::select('leverancier')->whereNotNull('leverancier')->distinct()->orderBy('leverancier')->pluck('leverancier');
        $categories = Category::orderBy('naam')->get();

        return view('materialen', compact('products','types','leveranciers','categories'));
    }

    public function create()
    {
        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            abort(403);
        }
        $categories = Category::orderBy('naam')->get();
        return view('nieuwmaterialen', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku', // stock keeping unit : word gebruikt voor unieke identificatie nog niet in het veld
            'leverancier' => 'nullable|string|max:255',
            'omschrijving' => 'nullable|string',
            'minimale_voorraad' => 'nullable|integer|min:0',
            'voorraad' => 'nullable|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('products', 'public');
            $data['foto_url'] = Storage::url($path);
        }

        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            abort(403);
        }

        $product = Product::create([
            'naam' => $data['naam'],
            'type' => $data['type'] ?? null,
            'sku' => $data['sku'] ?? null,
            'leverancier' => $data['leverancier'] ?? null,
            'omschrijving' => $data['omschrijving'] ?? null,
            'minimale_voorraad' => $data['minimale_voorraad'] ?? 0,
            'foto_url' => $data['foto_url'] ?? null,
            'categorie_id' => $data['categorie_id'] ?? null,
        ]);

        // create initial stock row (use provided 'voorraad' if any)
        $initial = intval($request->input('voorraad', 0));
        $stock = Stock::create([
            'product_id' => $product->id,
            'aantal' => $initial,
            'laatst_aangepast_op' => now(),
            'laatst_aangepast_door' => auth()->id() ?? 1,
        ]);

        if ($initial > 0) {
            StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id() ?? null,
                'wijziging_type' => 'bijleggen',
                'aantal' => $initial,
                'opmerking' => 'Initiële voorraad ingesteld',
                'datumtijd' => now(),
            ]);
        }

        return redirect()->route('materialen.index')->with('success','Product aangemaakt');
    }



    public function edit($id)
    {
        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            abort(403);
        }

        $product = Product::findOrFail($id);
        $categories = Category::orderBy('naam')->get();
        return view('nieuwmaterialen', compact('product','categories'));
    }

    public function update(Request $request, $id)
    {
        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            abort(403);
        }

        $product = Product::findOrFail($id);

        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'leverancier' => 'nullable|string|max:255',
            'omschrijving' => 'nullable|string',
            'minimale_voorraad' => 'nullable|integer|min:0',
            'voorraad' => 'nullable|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('products', 'public');
            $data['foto_url'] = Storage::url($path);
        }

            $product->update([
                'naam' => $data['naam'],
                'type' => $data['type'] ?? null,
                'sku' => $data['sku'] ?? $product->sku,
                'leverancier' => $data['leverancier'] ?? $product->leverancier,
                'omschrijving' => $data['omschrijving'] ?? null,
                'minimale_voorraad' => $data['minimale_voorraad'] ?? 0,
                'foto_url' => $data['foto_url'] ?? $product->foto_url,
                'categorie_id' => $data['categorie_id'] ?? $product->categorie_id,
            ]);

        // update stock if 'voorraad' provided
        if ($request->filled('voorraad')) {
            $requested = intval($request->input('voorraad'));
            $stock = Stock::firstOrCreate([
                'product_id' => $product->id,
            ], [
                'aantal' => 0,
                'laatst_aangepast_op' => now(),
                'laatst_aangepast_door' => auth()->id() ?? 1,
            ]);

            $delta = $requested - $stock->aantal;
            if ($delta !== 0) {
                // Use central adjust method to update stock and create log
                try {
                    \App\Models\Stock::adjust($product->id, $delta, auth()->id() ?? 1, 'Voorraad aangepast via bewerkformulier');
                } catch (\RuntimeException $e) {
                    $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
                    return redirect()->route($routeName)->with('error', $e->getMessage());
                }
            }
        }
        return redirect()->route('materialen.index')->with('success','Product bijgewerkt');
    }

    public function destroy($id)
    {
        $userRole = auth()->user()->role ?? '';
        // Alleen admin mag verwijderen
        if ($userRole !== 'admin') {
            abort(403);
        }

        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('materialen.index')->with('success','Product verwijderd');
    }

    public function pakken(Request $request, $id)
    {
        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            return response()->json(['error' => 'Niet gemachtigd'], 403);
        }

        $data = $request->validate([
            'aantal' => 'required|integer|min:1',
            'reden' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($id);

        try {
            $stock = Stock::adjust($product->id, -intval($data['aantal']), auth()->id() ?? 1, $data['reden'] ?? null);
            return response()->json(['success' => true, 'newAmount' => $stock->aantal]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function bijleggen(Request $request, $id)
    {
        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            return response()->json(['error' => 'Niet gemachtigd'], 403);
        }

        $data = $request->validate([
            'aantal' => 'required|integer|min:1',
            'reden' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($id);

        try {
            $stock = Stock::adjust($product->id, intval($data['aantal']), auth()->id() ?? 1, $data['reden'] ?? null);
            return response()->json(['success' => true, 'newAmount' => $stock->aantal]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Handle bulk actions on selected products.
     */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string',
            'selected' => 'required|array',
            'selected.*' => 'integer|distinct',
        ]);

        $action = $data['action'];
        $ids = $data['selected'];

        $userRole = auth()->user()->role ?? '';
        if (!in_array($userRole, ['admin','projectleider'])) {
            $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
            return redirect()->route($routeName)->with('error', 'Niet gemachtigd voor bulk-acties');
        }

        if (empty($ids)) {
            $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
            return redirect()->route($routeName)->with('error', 'Geen producten geselecteerd');
        }
        // Handle verwijderen
        if ($action === 'verwijderen') {
            // Alleen admin mag bulk verwijderen
            if (($userRole ?? '') !== 'admin') {
                $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
                return redirect()->route($routeName)->with('error', 'Alleen admin mag materialen verwijderen');
            }
            DB::transaction(function() use ($ids) {
                foreach ($ids as $id) {
                    $p = Product::find($id);
                    if (! $p) continue;
                    // delete related stock and logs
                    \App\Models\StockLog::where('product_id', $p->id)->delete();
                    \App\Models\Stock::where('product_id', $p->id)->delete();
                    $p->delete();
                }
            });

            $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
            return redirect()->route($routeName)->with('success', 'Geselecteerde producten verwijderd');
        }

        // Handle pakken / bijleggen in bulk (same amount for each selected product)
        if (in_array($action, ['pakken','bijleggen'])) {
            $amount = intval($request->input('bulk_amount', 0));
            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Ongeldige hoeveelheid opgegeven voor actie.');
            }

            $products = Product::whereIn('id', $ids)->with('stock')->get();

            // For pakken, ensure sufficient stock for all products
            if ($action === 'pakken') {
                $insufficient = [];
                foreach ($products as $p) {
                    $current = optional($p->stock)->aantal ?? 0;
                    if ($current < $amount) {
                        $insufficient[] = $p->naam ?? "ID {$p->id}";
                    }
                }
                if (count($insufficient) > 0) {
                    $list = implode(', ', array_slice($insufficient, 0, 10));
                    $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
                    return redirect()->route($routeName)->with('error', 'Onvoldoende voorraad voor: ' . $list);
                }
            }

            $reason = $request->input('bulk_reason') ?? null;
            DB::transaction(function() use ($products, $action, $amount, $reason) {
                foreach ($products as $p) {
                    $delta = $action === 'pakken' ? -$amount : $amount;
                    \App\Models\Stock::adjust($p->id, $delta, auth()->id() ?? 1, $reason ?? 'Bulk actie via dashboard');
                }
            });

            $routeName = $request->input('return_to') === 'dashboard' ? 'dashboard' : 'materialen.index';
            return redirect()->route($routeName)->with('success', 'Bulk-actie uitgevoerd');
        }

        return redirect()->back()->with('error', 'Onbekende actie');
    }
}
