<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Product;

class LogbookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $users = User::orderBy('name')->get();
        $products = Product::orderBy('naam')->get();

        $query = StockLog::with(['user', 'product'])->orderBy('datumtijd', 'desc');

        if ($request->filled('filter_wie')) {
            $query->where('user_id', $request->input('filter_wie'));
        }

        if ($request->filled('filter_product')) {
            $query->where('product_id', $request->input('filter_product'));
        }

        if ($request->filled('filter_actie')) {
            $query->where('wijziging_type', $request->input('filter_actie'));
        }

        if ($request->filled('filter_omschrijving')) {
            $query->where('opmerking', 'like', '%' . $request->input('filter_omschrijving') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('datumtijd', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('datumtijd', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->appends($request->query());

        return view('logboek', compact('logs', 'users', 'products'));
    }
}
