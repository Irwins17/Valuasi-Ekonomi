@extends('layouts.admin')
@section('page_title', 'Audit Log')
@section('page_subtitle', 'Riwayat perubahan data sistem')
@section('admin_content')
<div class="card">
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <select name="event" class="form-input" style="width:auto;padding:6px 30px 6px 10px;font-size:13px;">
                <option value="">Semua Event</option>
                <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
            <select name="table_name" class="form-input" style="width:auto;padding:6px 30px 6px 10px;font-size:13px;">
                <option value="">Semua Tabel</option>
                @foreach(['projects','eop_data','tcm_data','cvm_data','benefits','costs'] as $t)
                <option value="{{ $t }}" {{ request('table_name') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline">Filter</button>
            @if(request()->hasAny(['event','table_name']))
            <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Waktu</th><th>User</th><th>Event</th><th>Tabel</th><th>ID</th><th>Detail</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td style="font-size:13px;font-weight:500;">{{ $log->user?->name ?? 'System' }}</td>
                <td>
                    <span class="badge {{ $log->event === 'created' ? 'badge-success' : ($log->event === 'updated' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $log->event }}
                    </span>
                </td>
                <td style="font-size:13px;font-family:monospace;">{{ $log->table_name }}</td>
                <td style="font-size:13px;color:var(--text-muted);">#{{ $log->model_id }}</td>
                <td>
                    @if($log->old_values || $log->new_values)
                    <details>
                        <summary style="font-size:12px;color:var(--primary);cursor:pointer;">Lihat detail</summary>
                        <div style="margin-top:8px;font-size:11px;max-width:400px;overflow-x:auto;">
                            @if($log->old_values)<div style="margin-bottom:4px;"><strong>Before:</strong> <code style="font-size:11px;">{{ is_string($log->old_values) ? Str::limit($log->old_values, 200) : Str::limit(json_encode($log->old_values), 200) }}</code></div>@endif
                            @if($log->new_values)<div><strong>After:</strong> <code style="font-size:11px;">{{ is_string($log->new_values) ? Str::limit($log->new_values, 200) : Str::limit(json_encode($log->new_values), 200) }}</code></div>@endif
                        </div>
                    </details>
                    @else
                    <span style="font-size:12px;color:var(--text-muted);">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada log audit.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->withQueryString()->links() }}
</div>
@endsection
