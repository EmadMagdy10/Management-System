<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDOException;

class CommentController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'comment' => 'required|max:255',
                'project_id' => 'required|exists:projects,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'error' => $validate->errors()->toArray()
                ], 400);
            }

            $technicianId = Auth::id();
            $projectId = $request->project_id;

            $isTechnicianAssociated = Technician::where('id', $technicianId)
                ->whereHas('projects', function ($query) use ($projectId) {
                    $query->where('id', $projectId);
                })->exists();

            if (!$isTechnicianAssociated) {
                return response()->json([
                    'status' => 'failed',
                    'error' => 'Technician is not associated with the project'
                ], 403);
            }

            $comment = Comment::create([
                'comment' => $request->comment,
                'project_id' => $projectId,
                'technician_id' => $technicianId
            ]);

            if ($comment) {
                return response()->json([
                    'message' => 'created',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Error',
                ], 204);
            }
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updata(Request $request, $id)
    {
        try {
            $validate = Validator::make($request->all(), [
                'comment' => 'required|max:255'
            ]);
        } catch (PDOException $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
