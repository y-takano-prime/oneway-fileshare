@extends('layouts.app')

@section('content')
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">アクセスログ</h2>

    <div class="axon-card" style="padding:0;overflow:hidden">
        <table class="axon-table">
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
                        <td style="color:#001240;font-size:12px">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td style="font-weight:500">{{ $log->downloadUrl->sharedFile->original_name ?? '-' }}</td>
                        <td style="color:#001240;font-size:12px">{{ $log->ip_address }}</td>
                        <td>{{ $log->action }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;color:#7090CC;padding:2rem">ログがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
