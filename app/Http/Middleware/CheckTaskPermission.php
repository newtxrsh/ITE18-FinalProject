<?php

namespace App\Http\Middleware;

use App\Models\TaskCollaborator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTaskPermission
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has permission to access the specified task.
     * Permission is granted if the user is a collaborator on the task (exists in task_collaborators table).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Get task_id from route parameter (e.g., /tasks/{taskId})
        $taskId = $request->route('taskId') ?? $request->route('task_id') ?? $request->input('task_id');

        if (! $taskId) {
            return response()->json(['message' => 'Task ID is required.'], 400);
        }

        // Check if user is a collaborator on this task
        $hasPermission = TaskCollaborator::where('task_id', $taskId)
            ->where('user_id', $user->user_id)
            ->exists();

        if (! $hasPermission) {
            return response()->json([
                'message' => 'You do not have permission to access this task.',
            ], 403);
        }

        return $next($request);
    }
}
