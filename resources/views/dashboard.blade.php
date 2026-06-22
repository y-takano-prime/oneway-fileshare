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
    <a href="{{ route('urls.index', ['status' => ['done']]) }}" class="axon-stat">
        <div class="axon-stat-num" style="color:#0044CC">{{ $doneCount }}</div>
        <div class="axon-stat-label">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="color:#0044CC"><circle cx="8" cy="8" r="6.3" stroke="currentColor" stroke-width="1.6"/><path d="M5.3 8.2L7.2 10.1L10.8 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            DL済み
        </div>
    </a>
    <a href="{{ route('urls.index', ['status' => ['wait']]) }}" class="axon-stat">
        <div class="axon-stat-num" style="color:#9B6200">{{ $waitCount }}</div>
        <div class="axon-stat-label">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="color:#9B6200"><circle cx="8" cy="8" r="6.3" stroke="currentColor" stroke-width="1.6"/><path d="M8 4.5V8L10.3 9.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            未DL
        </div>
    </a>
    <a href="{{ route('urls.index', ['status' => ['expired']]) }}" class="axon-stat">
        <div class="axon-stat-num" style="color:#5C5C5C">{{ $expiredCount }}</div>
        <div class="axon-stat-label">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="color:#5C5C5C"><rect x="2" y="3.5" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.6"/><line x1="2" y1="6.5" x2="14" y2="6.5" stroke="currentColor" stroke-width="1.6"/><line x1="6" y1="9" x2="10" y2="12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="10" y1="9" x2="6" y2="12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            期限切れ
        </div>
    </a>
    <a href="{{ route('urls.index', ['status' => ['invalidated']]) }}" class="axon-stat">
        <div class="axon-stat-num" style="color:#CC0000">{{ $invalidatedCount }}</div>
        <div class="axon-stat-label">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="color:#CC0000"><circle cx="8" cy="8" r="6.3" stroke="currentColor" stroke-width="1.6"/><line x1="3.8" y1="12.2" x2="12.2" y2="3.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            無効化済み
        </div>
    </a>
    <a href="{{ Auth::user()->role === 'admin' ? route('admin.storage.index') : route('files.index') }}" class="axon-stat">
        <div class="axon-stat-num" style="font-size:18px">
            {{ $storageUsedMb }} MB<span style="font-size:11px;color:#7090CC"> / {{ round($storageCapMb / 1024, 1) }} GB</span>
        </div>
        <div class="axon-stat-label">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="color:#001240"><ellipse cx="8" cy="3.8" rx="5.5" ry="1.8" stroke="currentColor" stroke-width="1.5"/><path d="M2.5 3.8V12.2C2.5 13.2 4.9 14 8 14C11.1 14 13.5 13.2 13.5 12.2V3.8" stroke="currentColor" stroke-width="1.5"/><path d="M2.5 8C2.5 9 4.9 9.8 8 9.8C11.1 9.8 13.5 9 13.5 8" stroke="currentColor" stroke-width="1.5"/></svg>
            ストレージ（{{ $fileCount }}件）
        </div>
        <div class="axon-bar"><div class="axon-bar-fill{{ Auth::user()->role !== 'admin' && $storagePercent >= $storageWarningThreshold ? ' warn' : '' }}" style="width:{{ $storagePercent }}%"></div></div>
    </a>
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
                <th style="text-align:center">属性</th>
                <th>ファイル名</th>
                <th>作成日</th>
                <th>有効期限</th>
                <th style="white-space:nowrap;text-align:center">DL数</th>
                <th style="text-align:center">状態</th>
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
                <td style="text-align:center">
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
                <td style="white-space:nowrap;text-align:center">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
                <td style="text-align:center">
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
