<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 10);

        $employeeQuery = Employee::query();

        if ($id) {
            $employee = $employeeQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee,'Employee found');
            }

            return ResponseFormatter::error('Employee not Found', 404);
        }

        // Multiple Data
        $employees = $employeeQuery;

        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }


        if ($email) {
            $employees->where('email',$email);
        }

        if ($age) {
            $employees->where('age',$age);
        }

        if ($role_id) {
            $employees->where('role_id',$role_id);
        }

        if ($team_id) {
            $employees->where('team_id',$team_id);
        }


        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees found'
        );
    }


    public function create(CreateEmployeeRequest $request)
    {
        try {
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            $employee = Employee::create([
                'name' => $request->name,
                'photo' => $path,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'team_id' => $request->team_id
            ]);

            if (!$employee) {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee created');
        } catch (Exception $error) {
            // Return error response
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // Upload icon
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Update Employee
            $employee ->update([
                'name' => $request->name,
                'photo' => $path,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'team_id' => $request->team_id
            ]);

            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);

            if ($employee) {
                throw new Exception('Employee not found');
            }

            $employee->delete();

            return ResponseFormatter::success('Employee deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
