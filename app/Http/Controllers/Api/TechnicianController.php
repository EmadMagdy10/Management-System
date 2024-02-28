<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use Illuminate\Support\Facades\Validator;
use PDOException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class TechnicianController extends Controller
{
    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|max:255',
            'email' => 'required|email|unique:technicians,email',
            'password' => 'required|min:6',

        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'error' => $validate->errors()->toArray()
            ], 400);
        }

        try {

            if (Auth::guard('Admin-api')->check()) {
                $technician = Technician::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                if ($technician) {
                    return response()->json([
                        'message' => 'created',
                    ], 200);
                } else {
                    return response()->json([
                        'error' => 'Technician Not Created',
                    ], 204);
                }
            } else {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        try {
            $technicians = Technician::all();
            if ($technicians) {
                return response()->json([
                    'data' => $technicians,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Not Have Technician',
                ], 204);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $technician = Technician::find($id);
            if ($technician) {
                return response()->json([
                    'message' => 'Technician',
                    'data' => $technician,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Not Have Technician',
                ], 204);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $technician = Technician::find($id);
            if ($technician) {
                $technician->delete();
                return response()->json([
                    'message' => 'deleted',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'error' => 'Not Found'
                ], 404);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
