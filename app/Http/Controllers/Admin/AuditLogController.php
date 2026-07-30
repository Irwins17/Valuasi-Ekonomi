<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        $logs = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['event', 'table_name']),
        ]);
    }
}
