<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionViewController extends Controller
{    public function __construct()
    {
        $this->middleware('permission:show')->only(['index', 'show']);
        $this->middleware('permission:create')->only(['create', 'store']);
        $this->middleware('permission:edit')->only(['edit', 'update']);
        $this->middleware('permission:delete')->only(['destroy']);
    }
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions']);
        Permission::create(['name' => $request->name]);
        return redirect()->back()->with('success', 'Permission added.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission deleted.');
    }
    public function update(Request $request, Permission $permission)
{
    $request->validate(['name' => 'required|unique:permissions,name,' . $permission->id]);
    $permission->name = $request->name;
    $permission->save();

    return redirect()->back()->with('success', 'Permission updated.');
}

}
