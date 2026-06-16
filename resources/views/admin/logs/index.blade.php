@extends('layouts.app')

@section('content')
    <h2 class="mb-3">アクセスログ</h2>

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>日時</th>
                        <th>ファイル名</th>
                        <th>IPアドレス</th>
                        <th>アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->downloadUrl->sharedFile->original_name ?? '-' }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->action }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">ログがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
