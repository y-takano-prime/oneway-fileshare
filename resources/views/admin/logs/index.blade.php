@extends('layouts.app')

@section('content')
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">アクセスログ</h2>

    <div class="axon-card" style="padding:0;overflow:hidden">
        <table class="axon-table">
            <thead>
                <tr>
                    <th>日時</th>
                    <th>担当者</th>
                    <th>相手先</th>
                    <th>属性</th>
                    <th>ファイル名</th>
                    <th>IPアドレス</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php $url = $log->downloadUrl; @endphp
                    <tr>
                        <td style="color:#001240;font-size:12px;white-space:nowrap">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td style="white-space:nowrap">{{ optional($url->user)->name ?? '-' }}</td>
                        <td style="white-space:nowrap">{{ $url->recipient_name ?? '-' }}</td>
                        <td>
                            @if(optional($url)->category === 'business')
                                <span class="badge-business">取引先</span>
                            @elseif(optional($url)->category === 'recruitment')
                                <span class="badge-recruitment">採用</span>
                            @elseif(optional($url)->category === 'other')
                                <span class="badge-other">その他</span>
                            @else
                                <span style="color:#B0C0E0;font-size:12px">—</span>
                            @endif
                        </td>
                        <td style="font-weight:500">
                            @if($url)
                                <a href="{{ route('urls.show', $url) }}" style="color:#0066FF;text-decoration:none">{{ optional($url->sharedFile)->original_name ?? '-' }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td style="color:#001240;font-size:12px">{{ $log->ip_address }}</td>
                        <td>{{ $log->action_label }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#7090CC;padding:2rem">ログがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
