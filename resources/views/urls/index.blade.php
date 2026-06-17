@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">URL管理</h2>
    @if(Auth::user()->role !== 'admin')
    <a href="{{ route('urls.create') }}" class="btn-axon">+ 新規作成</a>
    @endif
</div>

{{-- 検索フォーム --}}
<div style="margin-bottom:1rem;max-width:520px">
    <form method="GET" action="{{ route('urls.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="sort"   value="{{ $sort }}">
        <input type="hidden" name="dir"    value="{{ $dir }}">
        <div style="display:flex;gap:8px;margin-bottom:6px">
            <input type="text" name="q" value="{{ request('q') }}" class="axon-input" placeholder="相手先名・メール・ファイル名で検索" style="flex:1">
            <button type="submit" class="btn-axon-outline" style="white-space:nowrap">検索</button>
        </div>
        @if(Auth::user()->role === 'admin')
        <div style="display:flex;gap:8px">
            <input type="text" name="staff_q" value="{{ $staffQ }}" class="axon-input" placeholder="担当者名で検索" style="flex:1">
            <button type="submit" class="btn-axon-outline" style="white-space:nowrap">検索</button>
        </div>
        @endif
        @if(request('q') || $staffQ)
        <div style="margin-top:6px">
            <a href="{{ route('urls.index', ['status' => $status, 'sort' => $sort, 'dir' => $dir]) }}" class="btn-axon-ghost">クリア</a>
        </div>
        @endif
    </form>
</div>

{{-- フィルタータブ --}}
@php
    $tabs = ['all' => '全件', 'wait' => '未DL', 'done' => 'DL済み', 'expired' => '期限切れ'];
    $baseParams = array_filter(['q' => request('q'), 'staff_q' => $staffQ, 'sort' => $sort, 'dir' => $dir]);
@endphp
<div style="display:flex;gap:4px;margin-bottom:1rem">
    @foreach($tabs as $key => $label)
    @php $isActive = $status === $key; @endphp
    <a href="{{ route('urls.index', array_merge($baseParams, ['status' => $key])) }}"
       style="font-size:12px;padding:5px 12px;border-radius:20px;text-decoration:none;border:1px solid;
              {{ $isActive ? 'background:#0066FF;color:#fff;border-color:#0066FF;font-weight:500' : 'background:#fff;color:#7090CC;border-color:#D0DEFF' }}">
        {{ $label }}
        <span style="font-size:11px;{{ $isActive ? 'opacity:.8' : 'color:#B0C0E0' }}">{{ $counts[$key] }}</span>
    </a>
    @endforeach
</div>

{{-- テーブル --}}
<div class="axon-card" style="padding:0;overflow:hidden">
    <table class="axon-table">
        <thead>
            <tr>
                @if(Auth::user()->role === 'admin')
                <th>担当者</th>
                @endif
                {{-- ソート可能ヘッダー --}}
                @php
                    function sortLink($route, $col, $label, $currentSort, $currentDir, $extra = []) {
                        $isActive = $currentSort === $col;
                        $nextDir  = ($isActive && $currentDir === 'desc') ? 'asc' : 'desc';
                        $arrow    = $isActive ? ($currentDir === 'asc' ? ' ↑' : ' ↓') : '';
                        $params   = array_merge($extra, ['sort' => $col, 'dir' => $nextDir]);
                        $url      = route($route, $params);
                        $weight   = $isActive ? 'font-weight:600;color:#0066FF' : 'color:inherit';
                        return "<a href=\"{$url}\" style=\"text-decoration:none;{$weight}\">{$label}{$arrow}</a>";
                    }
                    $sp = array_filter(['q' => request('q'), 'staff_q' => $staffQ, 'status' => $status]);
                @endphp
                <th>{!! sortLink('urls.index', 'recipient_name', '相手先', $sort, $dir, $sp) !!}</th>
                <th>メールアドレス</th>
                <th>属性</th>
                <th>ファイル名</th>
                <th>{!! sortLink('urls.index', 'created_at', '作成日', $sort, $dir, $sp) !!}</th>
                <th>{!! sortLink('urls.index', 'expires_at', '有効期限', $sort, $dir, $sp) !!}</th>
                <th>{!! sortLink('urls.index', 'download_count', 'DL数', $sort, $dir, $sp) !!}</th>
                <th>状態</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($urls as $url)
            @php
                $isExpired = $url->expires_at->isPast();
                $isDone    = $url->download_count > 0;
            @endphp
            <tr>
                @if(Auth::user()->role === 'admin')
                <td style="white-space:nowrap">{{ $url->user->name ?? '-' }}</td>
                @endif
                <td style="font-weight:500">{{ $url->recipient_name }}</td>
                <td style="color:#7090CC;font-size:12px">{{ $url->recipient_email }}</td>
                <td>
                    @if($url->category === 'business')
                        <span class="badge-business">取引先</span>
                    @elseif($url->category === 'recruitment')
                        <span class="badge-recruitment">採用</span>
                    @elseif($url->category === 'other')
                        <span class="badge-other">その他</span>
                    @else
                        <span style="color:#B0C0E0;font-size:12px">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('urls.show', $url) }}" style="color:#0066FF;text-decoration:none">
                        {{ $url->sharedFile->original_name ?? '-' }}
                    </a>
                </td>
                <td style="color:#7090CC;font-size:12px">{{ $url->created_at->format('Y-m-d') }}</td>
                <td style="font-size:12px">{{ $url->expires_at->format('Y-m-d H:i') }}</td>
                <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
                <td>
                    @if($isExpired)
                        <span class="badge-expired">期限切れ</span>
                    @elseif($isDone)
                        <span class="badge-dl">DL済み</span>
                    @else
                        <span class="badge-wait">未DL</span>
                    @endif
                </td>
                <td style="text-align:right;white-space:nowrap">
                    <a href="{{ route('urls.show', $url) }}" class="btn-axon-outline" style="padding:4px 10px;font-size:12px">詳細</a>
                    <form method="POST" action="{{ route('urls.destroy', $url) }}" class="d-inline" onsubmit="return confirm('無効化しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-axon-danger" style="padding:4px 10px;font-size:12px">無効化</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ Auth::user()->role === 'admin' ? 10 : 9 }}" style="text-align:center;color:#7090CC;padding:2rem">URLがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
