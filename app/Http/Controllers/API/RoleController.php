<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $with_responsibilites = $request->input('with_responsibilites', false);

        $roleQuery = Role::query();

        if ($id) {
            $role = $roleQuery->with('responsibilites')->find($id);

            if ($role) {
                return ResponseFormatter::success($role);
            }

            return ResponseFormatter::error('Role not Found', 404);
        }

        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($with_responsibilites) {
            $roles->with('responsibilites');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }


    public function create(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if (!$role) {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role created');
        } catch (Exception $error) {
            // Return error response
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }


    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if ($role) {
                throw new Exception('Role not found');
            }

            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
