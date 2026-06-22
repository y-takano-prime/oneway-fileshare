@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">ダッシュボード</h2>
    @if(Auth::user()->role !== 'admin')
    <a href="{{ route('urls.create') }}" class="btn-axon">+ 新規作成</a>
    @endif
</div>

@if(Auth::user()->role !== 'admin' && $storagePercent >= $storageWarningThreshold)
<div class="axon-alert-warning">
    ストレージ使用量が全体容量の{{ $storagePercent }}%に達しています（警告しきい値: {{ $storageWarningThreshold }}%）。不要なファイルの削除をご検討ください。
</div>
@endif

{{-- メトリクスカード --}}
<div class="axon-stat-grid" style="margin-bottom:1.25rem">
    <div class="axon-stat">
        <div class="axon-stat-num">{{ $totalUrls }}</div>
        <div class="axon-stat-label">TOTAL URLs</div>
    </div>
    <div class="axon-stat">
        <div class="axon-stat-num" style="color:#0066FF">{{ $doneCount }}</div>
        <div class="axon-stat-label">DOWNLOADED</div>
    </div>
    <div class="axon-stat">
        <div class="axon-stat-num" style="color:#D4880A">{{ $waitCount }}</div>
        <div class="axon-stat-label">PENDING</div>
    </div>
    <div class="axon-stat">
        <div class="axon-stat-num" style="font-size:18px">
            {{ $storageUsedMb }} MB<span style="font-size:11px;color:#7090CC"> / {{ round($storageCapMb / 1024, 1) }} GB</span>
        </div>
        <div class="axon-stat-label">STORAGE（{{ $fileCount }}件）</div>
        <div class="axon-bar"><div class="axon-bar-fill{{ Auth::user()->role !== 'admin' && $storagePercent >= $storageWarningThreshold ? ' warn' : '' }}" style="width:{{ $storagePercent }}%"></div></div>
    </div>
</div>

{{-- 直近URL一覧 --}}
<div class="axon-card" style="padding:0;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:13px;font-weight:500;color:#001240">直近のURL</span>
        <a href="{{ route('urls.index') }}" style="font-size:12px;color:#0066FF;text-decoration:none">すべて見る →</a>
    </div>
    <div class="axon-table-wrap">
    <table class="axon-table">
        <thead>
            <tr>
                @if(Auth::user()->role === 'admin')
                <th>担当者</th>
                @endif
                <th style="white-space:nowrap">相手先</th>
                <th>企業</th>
                <th>役職部署</th>
                <th>メールアドレス</th>
                <th>属性</th>
                <th>ファイル名</th>
                <th>作成日</th>
                <th>有効期限</th>
                <th style="white-space:nowrap">DL数</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentUrls as $url)
            @php
                $isExpired = $url->expires_at->isPast();
                $isDone    = $url->download_count > 0;
            @endphp
            <tr class="{{ $isExpired ? 'row-expired' : '' }}">
                @if(Auth::user()->role === 'admin')
                <td style="white-space:nowrap">{{ $url->user->name ?? '-' }}</td>
                @endif
                <td style="font-weight:500;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->recipient_name }}">{{ $url->recipient_name }}</td>
                <td style="color:#001240;font-size:13px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->company_name }}">{{ $url->company_name ?: '—' }}</td>
                <td style="color:#001240;font-size:13px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->recipient_title }}">{{ $url->recipient_title ?: '—' }}</td>
                <td style="color:#001240;font-size:13px">{{ $url->recipient_email }}</td>
                <td>
                    @if($url->category === 'business')
                        <span class="badge-business">取引先</span>
                    @elseif($url->category === 'recruitment')
                        <span class="badge-recruitment">採用</span>
                    @elseif($url->category === 'other')
                        <span class="badge-other">その他</span>
                    @else
                        <span style="color:#B0C0E0;font-size:13px">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('urls.show', $url) }}" style="color:#0066FF;text-decoration:none">
                        {{ $url->sharedFile->original_name ?? '-' }}
                    </a>
                </td>
                <td style="color:#001240;font-size:13px;white-space:nowrap">{{ $url->created_at->format('Y-m-d') }}</td>
                <td style="font-size:13px">{{ $url->expires_at->format('Y-m-d H:i') }}</td>
                <td style="white-space:nowrap">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
                <td>
                    @if($isExpired)
                        <span class="badge-expired">期限切れ</span>
                    @elseif($isDone)
                        <span class="badge-dl">DL済み</span>
                    @else
                        <span class="badge-wait">未DL</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ Auth::user()->role === 'admin' ? 11 : 10 }}" style="text-align:center;color:#7090CC;padding:2rem">データがありません</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
