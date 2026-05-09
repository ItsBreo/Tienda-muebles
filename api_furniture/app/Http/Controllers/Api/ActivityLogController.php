<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated listing of activity logs.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $logs = ActivityLog::latest()
            ->paginate(15);

        return $this->okResponse(
            $logs,
            'Activity logs retrieved successfully.'
        );
    }
}
