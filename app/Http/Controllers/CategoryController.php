<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categorieen');
    }

    public function create()
    {
        return view('categorieen_create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'omschrijving' => 'nullable|string',
        ]);

        $category = Category::create([
            'naam' => $data['naam'],
            'slug' => \Illuminate\Support\Str::slug($data['naam']),
            'omschrijving' => $data['omschrijving'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['id' => $category->id, 'naam' => $category->naam], 201);
        }

        return redirect()->route('categorieen.index');
    }

    public function edit($id)
    {
        return view('categorieen_create');
    }

    public function update(Request $request, $id)
    {
        // TODO: update
        return redirect()->route('categorieen.index');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categorieen.index');
    }
}
