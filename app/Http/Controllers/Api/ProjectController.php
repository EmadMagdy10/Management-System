<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\emailMailable;
use App\Models\Client;
use App\Models\Project;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PDOException;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $projects = Project::all();
                if ($projects->isNotEmpty()) {
                    return response()->json([
                        'data' => $projects,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'No Projects Found',

                    ], 204);
                }
            } else if (Auth::guard('technician-api')->check()) {
                $userId = Auth::guard('technician-api')->user()->getAuthIdentifier();
                $technician = Technician::find($userId);
                if ($technician) {
                    $projects = $technician->projects;
                } else {
                    return response()->json([
                        'message' => 'Technician Not Found'
                    ], 204);
                }
                if ($projects->isNotEmpty()) {
                    return response()->json([
                        'data' => $projects,
                    ], 200);
                } else if ($projects->isEmpty()) {
                    return response()->json([
                        'message' => 'No Projects Found',
                    ], 204);
                }
            } else {
                return response()->json([
                    'error' => 'Unauthorized',
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        if (Auth::guard('Admin-api')->check()) {
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'description' => 'required|max:255',
                'start_date' => 'required|date_format:Y-m-d',
                'due_date' => 'required|date_format:Y-m-d',
                'note_to_clients' => 'required|max:255',
                'status' => 'required|in:Open,In Progress,Completed,Closed,Rejected',
                'technician_id' => 'required|exists:technicians,id',
                'client_email' => 'required|email|exists:clients,email',

            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'error' => $validate->errors()->toArray()
                ], 400);
            }
            try {
                $currentDate = date('Y-m-d');
                $overdueProjects = Project::where('technician_id', $request->technician_id)
                    ->where('due_date', '<', $currentDate)
                    ->whereIn('status', ['Open', 'In Progress'])
                    ->get();

                if ($overdueProjects->count() > 0) {
                    return response()->json([
                        'status' => 'Failed',
                        'error' => 'The technician has overdue projects. All overdue projects must be closed before assigning a new project.'
                    ], 400);
                } else {

                    $project = Project::create([
                        'name' => $request->name,
                        'description' => $request->description,
                        'start_date' => $request->start_date,
                        'due_date' =>  $request->due_date,
                        'note_to_clients' => $request->note_to_clients,
                        'status' => $request->status,
                        'client_email' => $request->client_email,
                        'technician_id' => $request->technician_id,
                    ]);
                    if ($project) {
                        Mail::to($request->client_email)->send(new emailMailable($project));
                        return response()->json([
                            'message' => 'created',
                        ], 200);
                    } else {
                        return response()->json([
                            'message' => 'Error',
                        ], 204);
                    }
                }
            } catch (PDOException $e) {
                return response()->json([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }
    public function show($id)
    {
        try {
            if (Auth::guard('Admin-api')->check()) {
                $project = Project::find($id);
                if ($project) {
                    return response()->json([
                        'message' => 'Project',
                        'data' => $project,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Not Found ',
                    ], 404);
                }
            } else if (Auth::guard('technician-api')->check()) {
                $project = Technician::find(Auth::guard('technician-api')->user()->id)->projects()->get();
                if ($project) {
                    return response()->json([
                        'message' => 'done',
                        'data' => $project
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Not Found',

                    ], 200);
                }
            } else {
                return response()->json([
                    'error' => 'Unauthorized',
                ], 401);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        if (Auth::guard('Admin-api')->check()) {
            $validate = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'description' => 'required|max:255',
                'start_date' => 'required|date_format:Y-m-d',
                'due_date' => 'required|date_format:Y-m-d',
                'note_to_clients' => 'required|max:255',
                'status' => 'required|in:Open,In Progress,Completed,Closed,Rejected',
                'technician_id' => 'required|exists:technicians,id',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'error' => $validate->errors()->toArray()
                ]);
            }
            $project = Project::find($id);
            if ($project) {
                $project->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'start_date' => $request->start_date,
                    'due_date' =>  $request->due_date,
                    'note_to_clients' => $request->note_to_clients,
                    'status' => $request->status,
                    'technician_id' => $request->technician_id,
                ]);
                return response()->json([
                    'message' => 'project updated'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'project not found'
                ], 404);
            }
        } else if (Auth::guard('technician-api')->check()) {
            $validate = Validator::make($request->only('status'), [
                'status' => 'required|in:Open,In Progress,Completed',
            ]);
            if ($request->has('status') && count($request->all()) === 2) { //token and status
                if ($validate->fails()) {
                    return response()->json([
                        'status' => 'failed',
                        'error' => $validate->errors()->toArray()
                    ]);
                }
                $project = Project::where('id', $id)->where(
                    'technician_id',
                    Auth::guard('technician-api')->user()->id
                )->first();
                if ($project) {
                    $project->update($request->only('status'));
                    return response()->json([
                        'message' => 'project updated'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'project not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'you can not edit',
                ], 401);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function destroy($id)
    {
        try {
            $project = Project::find($id);
            if ($project) {
                $project->destroy($id);
                return response()->json([
                    'message' => 'project deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'project not found'
                ], 404);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function search(Request $request)
    {
        if (Auth::guard('Admin-api')->check()) {
            if (count($request->all()) === 1) {
                $projects = Project::all();
                return response()->json([
                    'status' => 'done',
                    'data' => $projects
                ], 200);
            } else {
                $projects = Project::where('name', $request->name)
                    ->orWhere('status', $request->status)
                    ->orWhere('due_date', $request->due_date)
                    ->orWhere('start_date', $request->start_date)
                    ->orwhereBetween('due_date', [$request->start_range_date, $request->due_range_date])
                    ->get();
            }
        } else if (Auth::guard('technician-api')->check()) {
            if (count($request->all()) === 1) {
                $projects = Project::all()->where('technician_id', Auth::guard('technician-api')->user()->id);
                return response()->json([
                    'status' => 'done',
                    'data' => $projects
                ], 200);
            } else {
                $projects = Project::where('technician_id', Auth::guard('technician-api')->user()->id)
                    ->where(function ($query) use ($request) {
                        $query->where('name', $request->name)
                            ->orWhere('status', $request->status)
                            ->orWhere('due_date', $request->due_date)
                            ->orWhere('start_date', $request->start_date)
                            ->orWhereBetween('due_date', [$request->start_range_date, $request->due_range_date]);
                    })
                    ->get();
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 400);
        }

        if ($projects->isNotEmpty()) {
            return response()->json([
                'data' => $projects
            ]);
        } else {
            return response()->json([
                'data' => 'not found'
            ]);
        }
    }
}
