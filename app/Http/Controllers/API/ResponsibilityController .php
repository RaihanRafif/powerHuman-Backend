<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResposibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility);
            }

            return ResponseFormatter::error('Responsibility not Found', 404);
        }

        $responsibilities = $responsibilityQuery->where('responsibility_id', $request->responsibility_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibilities found'
        );
    }


    public function create(CreateResposibilityRequest $request)
    {
        try {
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'responsibility_id' => $request->responsibility_id
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility created');
        } catch (Exception $error) {
            // Return error response
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $responsibility = Responsibility::find($id);

            if ($responsibility) {
                throw new Exception('Responsibility not found');
            }

            $responsibility->delete();

            return ResponseFormatter::success('Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
