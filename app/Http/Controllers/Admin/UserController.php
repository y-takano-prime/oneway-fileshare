<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::leftJoin('depts', 'users.dept_id', '=', 'depts.id')
            ->select('users.*', 'depts.name as dept_name')
            ->latest('users.created_at')
            ->paginate(20);

        return view('admin.users.index', ['users' => $users]);
    }

    private function depts()
    {
        return DB::table('depts')->whereNull('deleted_at')->orderBy('sort_number')->get();
    }

    public function create()
    {
        return view('admin.users.create', ['depts' => $this->depts()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:4', 'confirmed'],
            'role'          => ['required', 'in:admin,staff'],
            'is_active'     => ['boolean'],
            'company_id'    => ['nullable', 'in:P,M,T,H'],
            'dept_id'       => ['nullable', 'exists:depts,id'],
        ]);

        User::create([
            'name'          => $validated['name'],
            'employee_code' => $validated['employee_code'] ?? null,
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'is_active'     => $request->boolean('is_active'),
            'company_id'    => $validated['company_id'] ?? null,
            'dept_id'       => $validated['dept_id'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'ユーザーを作成しました');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user, 'depts' => $this->depts()]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'email'         => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'      => ['nullable', 'string', 'min:4', 'confirmed'],
            'role'          => ['required', 'in:admin,staff'],
            'is_active'     => ['boolean'],
            'company_id'    => ['nullable', 'in:P,M,T,H'],
            'dept_id'       => ['nullable', 'exists:depts,id'],
        ]);

        $user->name          = $validated['name'];
        $user->employee_code = $validated['employee_code'] ?? null;
        $user->email         = $validated['email'];
        $user->role          = $validated['role'];
        $user->is_active     = $request->boolean('is_active');
        $user->company_id    = $validated['company_id'] ?? null;
        $user->dept_id       = $validated['dept_id'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'ユーザーを更新しました');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身は削除できません');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'ユーザーを削除しました');
    }
}
