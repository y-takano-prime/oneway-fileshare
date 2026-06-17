@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">新規作成</h2>

{{-- ステップバー --}}
<div class="axon-steps">
    <div class="axon-step">1. ファイル選択</div>
    <div class="axon-step active">2. 送付先設定</div>
    <div class="axon-step">3. メール確認</div>
</div>

{{-- 選択ファイル表示 --}}
<div class="axon-card" style="margin-bottom:1rem;display:flex;align-items:center;gap:10px">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
    <span style="font-size:13px;font-weight:500;color:#001240">{{ $file->original_name }}</span>
    <span style="font-size:12px;color:#7090CC;margin-left:auto">{{ round($file->file_size / 1024, 1) }} KB</span>
</div>

@if($errors->any())
<div class="axon-alert-error">
    <ul style="margin:0;padding-left:1.2em">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="axon-card">
    <form method="POST" action="{{ route('urls.store') }}">
        @csrf
        <div style="margin-bottom:1rem">
            <label class="axon-label">属性 <span style="color:#CC0000">*</span></label>
            <div style="display:flex;gap:8px;margin-top:4px">
                @foreach(['business' => ['label'=>'取引先','class'=>'badge-business'], 'recruitment' => ['label'=>'採用','class'=>'badge-recruitment'], 'other' => ['label'=>'その他','class'=>'badge-other']] as $val => $opt)
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:6px 14px;border:1px solid #D0DEFF;border-radius:6px;{{ old('category') === $val ? 'border-color:#0066FF;background:#F0F5FF' : '' }}">
                    <input type="radio" name="category" value="{{ $val }}" style="accent-color:#0066FF" {{ old('category') === $val ? 'checked' : '' }} required>
                    <span class="{{ $opt['class'] }}">{{ $opt['label'] }}</span>
                </label>
                @endforeach
            </div>
            @error('category')<div style="color:#CC0000;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>
        <div id="biz-fields">
            <div style="margin-bottom:1rem">
                <label class="axon-label">企業名（任意）</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" class="axon-input" placeholder="例：株式会社〇〇">
            </div>
            <div style="margin-bottom:1rem">
                <label class="axon-label">役職・部署（任意）</label>
                <input type="text" name="recipient_title" value="{{ old('recipient_title') }}" class="axon-input" placeholder="例：営業部 部長">
            </div>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">相手先の名前</label>
            <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">相手先メールアドレス</label>
            <input type="email" name="recipient_email" value="{{ old('recipient_email') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">有効期限</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">ダウンロード回数上限（任意）</label>
            <input type="number" name="download_limit" value="{{ old('download_limit') }}" class="axon-input" min="1" max="9999">
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">備考（任意）</label>
            <textarea name="memo" class="axon-input" rows="3" style="resize:vertical" placeholder="社内メモ・送付の目的など">{{ old('memo') }}</textarea>
        </div>
        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="notify_on_download" value="1" id="notify_on_download" {{ old('notify_on_download') ? 'checked' : '' }} style="accent-color:#0066FF;width:15px;height:15px">
            <label for="notify_on_download" style="font-size:13px;color:#001240;cursor:pointer">ダウンロード時に通知する</label>
        </div>
        <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:1px solid #D4DFF5">
            <a href="{{ route('urls.create') }}" class="btn-axon-ghost">← 戻る</a>
            <button type="submit" class="btn-axon">URLを発行する →</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function toggleBizFields() {
    const checked = document.querySelector('input[name="category"]:checked');
    const biz = document.getElementById('biz-fields');
    if (checked && checked.value === 'recruitment') {
        biz.style.display = 'none';
        biz.querySelectorAll('input').forEach(function(el) { el.value = ''; });
    } else {
        biz.style.display = '';
    }
}

document.querySelectorAll('input[name="category"]').forEach(function(radio) {
    radio.addEventListener('change', toggleBizFields);
});

toggleBizFields();
</script>
@endsection
