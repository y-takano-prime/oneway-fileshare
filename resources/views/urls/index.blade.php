@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">URL管理</h2>
    @if(Auth::user()->role !== 'admin')
    <a href="{{ route('urls.create') }}" class="btn-axon">+ 新規作成</a>
    @endif
</div>

{{-- 検索・絞り込みフォーム --}}
@php
    $statusFilters = ['wait' => '未DL', 'done' => 'DL済み', 'expired' => '期限切れ', 'invalidated' => '無効化済み'];
    $categoryFilters = ['business' => '取引先', 'recruitment' => '採用', 'other' => 'その他'];
@endphp
<div class="axon-card" style="margin-bottom:1.25rem">
    <form method="GET" action="{{ route('urls.index') }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir"  value="{{ $dir }}">

        <div style="display:flex;gap:8px;margin-bottom:10px;max-width:520px">
            <input type="text" name="q" value="{{ request('q') }}" class="axon-input" placeholder="相手先名・メール・ファイル名で検索" style="flex:1">
        </div>
        @if(Auth::user()->role === 'admin')
        <div style="display:flex;gap:8px;margin-bottom:14px;max-width:520px">
            <input type="text" name="staff_q" value="{{ $staffQ }}" class="axon-input" placeholder="担当者名で検索" style="flex:1">
        </div>
        @endif

        <div style="margin-bottom:12px">
            <div class="axon-label" style="margin-bottom:6px">状態（複数選択可）</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @foreach($statusFilters as $key => $label)
                <label class="axon-checkbox-pill">
                    <input type="checkbox" name="status[]" value="{{ $key }}" {{ in_array($key, $selectedStatuses) ? 'checked' : '' }}>
                    {{ $label }}
                    <span class="axon-checkbox-pill-count">{{ $counts[$key] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom:14px">
            <div class="axon-label" style="margin-bottom:6px">属性（複数選択可）</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @foreach($categoryFilters as $key => $label)
                <label class="axon-checkbox-pill">
                    <input type="checkbox" name="category[]" value="{{ $key }}" {{ in_array($key, $selectedCategories) ? 'checked' : '' }}>
                    {{ $label }}
                    <span class="axon-checkbox-pill-count">{{ $categoryCounts[$key] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:8px;align-items:center;padding-top:12px;border-top:1px solid #D4DFF5">
            <button type="submit" class="btn-axon">絞り込む</button>
            @if(request('q') || $staffQ || $selectedStatuses || $selectedCategories)
            <a href="{{ route('urls.index', array_filter(['sort' => $sort, 'dir' => $dir])) }}" class="btn-axon-ghost">クリア</a>
            @endif
        </div>
    </form>
</div>

{{-- テーブル --}}
<div class="axon-card" style="padding:0;overflow:hidden">
    <div class="axon-table-wrap">
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
                    $sp = array_filter(['q' => request('q'), 'staff_q' => $staffQ, 'status' => $selectedStatuses, 'category' => $selectedCategories]);
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
            <tr class="{{ $isInvalidated ? 'row-invalidated' : ($isExpired ? 'row-expired' : '') }}">
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
    </div>
    <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
        {{ $urls->links() }}
    </div>
</div>
@endsection
