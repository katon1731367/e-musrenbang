<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        Session::put('page_title', 'User');
        Session::put('menu', 'User');
    }

    public function index()
    {
        if (!(request()->user()->can('viewAny~User')))
            return abort(403, 'unauthorized access');

        $users = User::with(['roles'])->get();
        if (request()->ajax()) {
            return $users;
        }

        $roles = Role::all();

        return view('administration.user.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        if (!(request()->user()->can('view~User')))
            return abort(403, 'unauthorized access');

        $validated = $request->validate([
            'name' => ['required', 'unique:users'],
            'role' => ['required', 'exists:roles,name'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:5'],
        ]);

        User::create(collect($request)->only(['name', 'email', 'password'])->toArray())
            ->syncRoles([$request['role']]);

        return Response()->json([
            'content' => 'user ' . $validated['name'] . ' created!',
            'type' => 'success'
        ]);
    }

    public function edit(string $id)
    {
        if (!(request()->user()->can('update~User')))
            return abort(403, 'unauthorized access');

        $user = User::with('roles')->find($id);
        return Response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        if (!(request()->user()->can('update~User')))
            return abort(403, 'unauthorized access');

        $validated = $request->validate([
            'name' => [
                'required',
                Rule::unique('users', 'name')->ignore($id)
            ],
            'email' => [
                'required',
                Rule::unique('users', 'email')->ignore($id)
            ],
            'role' => ['required', 'exists:roles,name'],
            'password' => 'sometimes|confirmed|min:5',
        ]);

        $user = User::find($id);
        $user->syncRoles([$request['role']]);
        $user->update(collect($request)->only(['name', 'email', 'password'])->toArray());

        return Response()->json([
            'content' => "user {$user->name} updated!",
            'type' => 'success'
        ]);
    }

    public function destroy(string $id)
    {
        if (!(request()->user()->can('delete~User')))
            return abort(403, 'unauthorized access');

        $user = User::find($id);
        $user->delete();

        return Response()->json([
            'content' => 'user ' . $user['name'] . ' deleted!',
            'type' => 'success'
        ]);
    }
}
