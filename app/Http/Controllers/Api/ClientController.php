<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDOException;

class ClientController extends Controller
{
    public function index()
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $clients = Client::all();
                if ($clients) {
                    return response()->json([
                        'data' => $clients
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Data Not Found'
                    ], 404);
                }
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request)
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $validate = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'email' => 'required|email|unique:clients,email',
                ]);
                if ($validate->fails()) {
                    return response()->json([
                        'status' => 'Failed',
                        'error' => $validate->errors()->toArray()
                    ], 400);
                }
                $client = Client::create([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
                if ($client) {
                    return response()->json([
                        'message' => 'Client Created',
                        'data' => $client,
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'You Must Be An Admin',
                    'error' => 'Unauthorized'
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $client = Client::findOrFail($id);
                if ($client) {
                    return response()->json([
                        'data' => $client
                    ], 200);
                } else {
                    return response()->json([
                        'meassage' => 'Client Not Found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'You Must Be An Admin',
                    'error' => 'Unauthorized'
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Faield',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $client = Client::where('email', $request->email)->orWhere('name', $request->name);
                if ($client) {
                    return response()->json([
                        'data' => $client
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Not Found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'You Must Be An Admin',
                    'error' => 'Unauthorized'
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $client = Client::find($id);
            if ($client) {
                $client->destroy($id);
                return response()->json([
                    'message' => 'Client Deleted'
                ], 202);
            } else {
                return response()->json([
                    'message' => 'Client Not Found'
                ], 404);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
