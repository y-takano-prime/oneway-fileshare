@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">ストレージ</h2>

{{-- 全体使用量 --}}
<div class="axon-card" style="margin-bottom:1.25rem">
    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px">
        <span style="font-size:13px;font-weight:500;color:#001240">全体使用量</span>
        <span style="font-size:13px;color:#001240">{{ $storageUsedMb }} / {{ $storageCapMb }} MB</span>
    </div>
    <div class="axon-bar"><div class="axon-bar-fill" style="width:{{ $storagePercent }}%"></div></div>
</div>

{{-- ユーザー別内訳 --}}
<div class="axon-card" style="padding:0;overflow:hidden;margin-bottom:1.25rem">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF">
        <span style="font-size:13px;font-weight:500;color:#001240">ユーザー別内訳</span>
    </div>
    <table class="axon-table">
        <thead>
            <tr>
                <th>ユーザー</th>
                <th>ファイル数</th>
                <th>使用量</th>
                <th>割合</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byUser as $row)
            @php
                $rowMb = round($row->total_size / 1024 / 1024, 1);
                $rowPercent = $totalSize > 0 ? round($row->total_size / $totalSize * 100) : 0;
            @endphp
            <tr>
                <td style="font-weight:500;white-space:nowrap">{{ optional($row->user)->name ?? '(削除済みユーザー)' }}</td>
                <td>{{ $row->file_count }}件</td>
                <td style="color:#001240;font-size:12px;white-space:nowrap">{{ $rowMb }} MB</td>
                <td style="width:160px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div class="axon-bar" style="flex:1;margin-top:0">
                            <div class="axon-bar-fill" style="width:{{ $rowPercent }}%"></div>
                        </div>
                        <span style="font-size:11px;color:#7090CC;white-space:nowrap">{{ $rowPercent }}%</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#7090CC;padding:2rem">ファイルがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- 大きいファイル --}}
<div class="axon-card" style="padding:0;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF">
        <span style="font-size:13px;font-weight:500;color:#001240">サイズの大きいファイル（上位20件）</span>
    </div>
    <table class="axon-table">
        <thead>
            <tr>
                <th>ファイル名</th>
                <th>アップロード者</th>
                <th>サイズ</th>
                <th>アップロード日</th>
            </tr>
        </thead>
        <tbody>
            @forelse($largestFiles as $file)
            <tr>
                <td style="font-weight:500;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $file->original_name }}">{{ $file->original_name }}</td>
                <td style="white-space:nowrap">{{ optional($file->user)->name ?? '(削除済みユーザー)' }}</td>
                <td style="color:#001240;font-size:12px;white-space:nowrap">{{ round($file->file_size / 1024 / 1024, 1) }} MB</td>
                <td style="color:#001240;font-size:12px;white-space:nowrap">{{ $file->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#7090CC;padding:2rem">ファイルがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
