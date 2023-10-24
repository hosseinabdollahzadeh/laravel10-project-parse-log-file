<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function countLogs(Request $request)
    {
        $serviceNames = $request->input('serviceNames', []);
        $statusCode = $request->input('statusCode');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Convert start and end dates to timestamps
        $startTimestamp = $startDate ? strtotime($startDate) : null;
        $endTimestamp = $endDate ? strtotime($endDate) : null;

        // Build the query with the provided filters
        $query = Log::query();
        $query->when(!empty($serviceNames), function ($q) use ($serviceNames) {
            return $q->whereIn('service_name', $serviceNames);
        });
        $query->when($statusCode, function ($q) use ($statusCode) {
            return $q->where('status_code', $statusCode);
        });
        $query->when($startDate, function ($q) use ($startTimestamp) {
            return $q->where('timestamp', '>=', $startTimestamp);
        });
        $query->when($endDate, function ($q) use ($endTimestamp) {
            return $q->where('timestamp', '<=', $endTimestamp);
        });

        // Get the count of matching rows
        $count = $query->count();

        // Return the result as JSON response
        return response()->json(['count' => $count]);
    }
}
