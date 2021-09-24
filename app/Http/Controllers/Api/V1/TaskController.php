<?php

namespace App\Http\Controllers\Api\V1;

use App\Tasks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Status;
use App\User;
use Deployer\Task\Task;

class TaskController extends Controller
{
    public function index() {
        $tasks = Status::with('tasks')->get();
        // \Log::error(json_decode($test));
        // $tasks = Tasks::all();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function update(Request $request) {
        $request->validate([
            'name' => 'required'
        ]);

        $data = [
            'name' => $request->name
        ];

        $task = Tasks::where('id', $request->id)->first();

        if ( isset($task) ) {
            $task->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task successfully updated.',
            'data' => $task
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required'
        ]);

        $data = [
            'name' => $request->name,
            'status_id' => $request->status_id,
            'user_ids' => $request->user_ids
        ];

        $task = Tasks::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Task successfully added.',
            'data' => $task
        ]);
    }

    public function deleteTask(Request $request) {
        $task = Tasks::where('id', $request->id)->first();
        $success = true;
        $message = '';

        if ( isset($task) ) {

            $task->delete();
            $message = "Task successfully deleted.";

        } else {
            $success = false;
            $message = "Task not found.";
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function updateStatus(Request $request) {
        $task = Tasks::where('id', $request->id)->first();
        $success = true;
        $message = '';

        $data = [
            'status_id' => $request->new_status_id,
        ];

        if ( isset($task) ) {
            $task->update($data);
            $message = "Task successfully updated to new status.";

        } else {
            $success = false;
            $message = "Task not found.";
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function fetchAssignedUsers($id) {
        $task = Tasks::find($id);
        $ids = $task->user_ids ?? [];

        $users = User::whereIn('id', $ids)->get();

        return response()->json([
            'success' => true,
            'msg' => 'Success',
            'data' => $users
        ]);
    }

    public function assignUserToTask(Request $request, $id) {
        $assignUserTotask = Tasks::firstOrCreate(['id' => $id]);
        $assignUserTotask->user_ids = $request->allUsers;
        $assignUserTotask->save();

        return response()->json([
            'success' => true,
            'msg' => 'User was assigned successfully.',
            'data' => User::whereIn('id', $request->allUsers)->get()
        ]);
    }
}
