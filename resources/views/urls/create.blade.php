@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">新規作成</h2>

{{-- ステップバー --}}
<div class="axon-steps">
    <div class="axon-step active">1. 送付先設定</div>
    <div class="axon-step">2. ファイル選択</div>
    <div class="axon-step">3. メール確認</div>
</div>

@if($errors->any())
<div class="axon-alert-error">
    <ul style="margin:0;padding-left:1.2em">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="axon-card">
    <form method="POST" action="{{ route('urls.store_step1') }}">
        @csrf
        @if($preselectedFileId)
        <input type="hidden" name="shared_file_id" value="{{ $preselectedFileId }}">
        @endif
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
            <label class="axon-label">相手先の名前 <span style="color:#CC0000">*</span></label>
            <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">相手先メールアドレス <span style="color:#CC0000">*</span></label>
            <input type="email" name="recipient_email" value="{{ old('recipient_email') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">有効期限 <span style="color:#CC0000">*</span></label>
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
        <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #D4DFF5">
            <button type="submit" class="btn-axon">次へ →</button>
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
