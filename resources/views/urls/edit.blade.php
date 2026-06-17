@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">URL編集</h2>
    <a href="{{ route('urls.show', $url) }}" class="btn-axon-ghost">← 詳細へ戻る</a>
</div>

@if($errors->any())
<div class="axon-alert-error">
    <ul style="margin:0;padding-left:1.2em">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="axon-card" style="max-width:480px">
    <form method="POST" action="{{ route('urls.update', $url) }}">
        @csrf
        @method('PUT')
        <div style="margin-bottom:1rem">
            <label class="axon-label">有効期限</label>
            <input type="datetime-local" name="expires_at"
                value="{{ old('expires_at', $url->expires_at->format('Y-m-d\TH:i')) }}"
                class="axon-input">
            @error('expires_at')
                <div style="color:#CC0000;font-size:12px;margin-top:4px">{{ $message }}</div>
            @enderror
        </div>
        <div style="margin-bottom:1.25rem">
            <label class="axon-label">ダウンロード上限（空欄＝無制限）</label>
            <input type="number" name="download_limit"
                value="{{ old('download_limit', $url->download_limit) }}"
                class="axon-input" min="1" max="9999">
            @error('download_limit')
                <div style="color:#CC0000;font-size:12px;margin-top:4px">{{ $message }}</div>
            @enderror
        </div>
        <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid #D4DFF5">
            <button type="submit" class="btn-axon">更新</button>
            <a href="{{ route('urls.show', $url) }}" class="btn-axon-ghost">キャンセル</a>
        </div>
    </form>
</div>
@endsection
