<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (! $user || ($user->role ?? '') !== 'admin') {
                abort(403, 'Alleen toegankelijk voor admin');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = \App\Models\User::query()->orderBy('created_at','desc');

        if ($request->filled('filter_naam')) {
            $query->where('name', 'like', '%' . $request->input('filter_naam') . '%');
        }
        if ($request->filled('filter_email')) {
            $query->where('email', 'like', '%' . $request->input('filter_email') . '%');
        }
        if ($request->filled('filter_rol')) {
            $query->where('role', $request->input('filter_rol'));
        }

        $users = $query->paginate(15);
        return view('gebruikers', compact('users'));
    }

    public function create()
    {
        return view('nieuwgebruikers');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => $data['role'] ?? 'medewerker',
        ]);

        return redirect()->route('gebruikers.index')->with('success','Gebruiker aangemaakt');
    }

    public function edit($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return view('nieuwgebruikers', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $data = $request->validated();

        // Voorkom het degraderen van de laatste admin
        if (isset($data['role']) && $user->role === 'admin' && $data['role'] !== 'admin') {
            $adminCount = \App\Models\User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('gebruikers.index')->with('error', 'Kan de laatste admin niet demoten.');
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        if (isset($data['role'])) $user->role = $data['role'];
        $user->save();

        return redirect()->route('gebruikers.index')->with('success','Gebruiker bijgewerkt');
    }

    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->route('gebruikers.index')->with('error','Je kunt jezelf niet verwijderen');
        }
        // Voorkom het verwijderen van de laatste admin
        if ($user->role === 'admin') {
            $adminCount = \App\Models\User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('gebruikers.index')->with('error','Kan de laatste admin niet verwijderen');
            }
        }

        $user->delete();
        return redirect()->route('gebruikers.index')->with('success','Gebruiker verwijderd');
    }

    public function changeRole(Request $request, $id)
    {
        $data = $request->validate([
            'role' => 'required|in:admin,medewerker,projectleider',
        ]);
        $user = \App\Models\User::findOrFail($id);

        // Voorkom het degraderen van de laatste admin via AJAX
        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            $adminCount = \App\Models\User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json(['success' => false, 'message' => 'Kan de laatste admin niet demoten'], 422);
            }
        }

        $user->role = $data['role'];
        $user->save();
        return response()->json(['success' => true, 'role' => $user->role]);
    }

    public function account()
    {
        return view('account');
    }

    public function updatePassword(Request $request, $id)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = \App\Models\User::findOrFail($id);

        // Optioneel: voorkomen dat de laatste admin onbruikbaar wordt gemaakt
        // (niet van toepassing omdat we valideren op minimumlengte en bevestiging)

        $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        $user->save();

        return response()->json(['success' => true]);
    }
}
