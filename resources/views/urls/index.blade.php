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
        <input type="hidden" name="status"   value="{{ $status }}">
        <input type="hidden" name="category" value="{{ $category }}">
        <input type="hidden" name="sort"     value="{{ $sort }}">
        <input type="hidden" name="dir"      value="{{ $dir }}">
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
            <a href="{{ route('urls.index', ['status' => $status, 'category' => $category, 'sort' => $sort, 'dir' => $dir]) }}" class="btn-axon-ghost">クリア</a>
        </div>
        @endif
    </form>
</div>

{{-- フィルタータブ --}}
@php
    $tabs = ['all' => '全件', 'wait' => '未DL', 'done' => 'DL済み', 'expired' => '期限切れ', 'invalidated' => '無効化済み'];
    $categoryTabs = ['all' => '全属性', 'business' => '取引先', 'recruitment' => '採用', 'other' => 'その他'];
    $baseParams = array_filter(['q' => request('q'), 'staff_q' => $staffQ, 'sort' => $sort, 'dir' => $dir, 'status' => $status, 'category' => $category]);
@endphp
<div style="display:flex;gap:4px;margin-bottom:8px">
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
@php
    $categoryColors = ['all' => '#001240', 'business' => '#0044CC', 'recruitment' => '#006E42', 'other' => '#5500AA'];
@endphp
<div style="display:flex;gap:4px;margin-bottom:1rem">
    @foreach($categoryTabs as $key => $label)
    @php $isActive = $category === $key; $activeColor = $categoryColors[$key]; @endphp
    <a href="{{ route('urls.index', array_merge($baseParams, ['category' => $key])) }}"
       style="font-size:12px;padding:5px 12px;border-radius:20px;text-decoration:none;border:1px solid;
              {{ $isActive ? "background:{$activeColor};color:#fff;border-color:{$activeColor};font-weight:500" : 'background:#fff;color:#7090CC;border-color:#D0DEFF' }}">
        {{ $label }}
        <span style="font-size:11px;{{ $isActive ? 'opacity:.8' : 'color:#B0C0E0' }}">{{ $categoryCounts[$key] }}</span>
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
                        return "<a href=\"{$url}\" style=\"text-decoration:none;color:inherit\">{$label}{$arrow}</a>";
                    }
                    $sp = array_filter(['q' => request('q'), 'staff_q' => $staffQ, 'status' => $status, 'category' => $category]);
                @endphp
                <th style="white-space:nowrap">{!! sortLink('urls.index', 'recipient_name', '相手先', $sort, $dir, $sp) !!}</th>
                <th>企業</th>
                <th>役職部署</th>
                <th>メールアドレス</th>
                <th>{!! sortLink('urls.index', 'category', '属性', $sort, $dir, $sp) !!}</th>
                <th>ファイル名</th>
                <th>{!! sortLink('urls.index', 'created_at', '作成日', $sort, $dir, $sp) !!}</th>
                <th>{!! sortLink('urls.index', 'expires_at', '有効期限', $sort, $dir, $sp) !!}</th>
                <th style="white-space:nowrap">{!! sortLink('urls.index', 'download_count', 'DL数', $sort, $dir, $sp) !!}</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            @forelse($urls as $url)
            @php
                $isInvalidated = $url->trashed();
                $isExpired = $url->expires_at->isPast();
                $isDone    = $url->download_count > 0;
            @endphp
            <tr>
                @if(Auth::user()->role === 'admin')
                <td style="white-space:nowrap">{{ $url->user->name ?? '-' }}</td>
                @endif
                <td style="font-weight:500;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->recipient_name }}">{{ $url->recipient_name }}</td>
                <td style="color:#001240;font-size:12px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->company_name }}">{{ $url->company_name ?: '—' }}</td>
                <td style="color:#001240;font-size:12px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $url->recipient_title }}">{{ $url->recipient_title ?: '—' }}</td>
                <td style="color:#001240;font-size:12px">{{ $url->recipient_email }}</td>
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
                <td style="color:#001240;font-size:12px;white-space:nowrap">{{ $url->created_at->format('Y-m-d') }}</td>
                <td style="font-size:12px">{{ $url->expires_at->format('Y-m-d H:i') }}</td>
                <td style="white-space:nowrap">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
                <td>
                    @if($isInvalidated)
                        <span class="badge-invalidated">無効化済み</span>
                    @elseif($isExpired)
                        <span class="badge-expired">期限切れ</span>
                    @elseif($isDone)
                        <span class="badge-dl">DL済み</span>
                    @else
                        <span class="badge-wait">未DL</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ Auth::user()->role === 'admin' ? 11 : 10 }}" style="text-align:center;color:#7090CC;padding:2rem">URLがありません</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
        {{ $urls->links() }}
    </div>
</div>
@endsection
