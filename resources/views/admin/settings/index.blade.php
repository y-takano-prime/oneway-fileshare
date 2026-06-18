@extends('layouts.app')

@section('content')
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">システム設定</h2>

    @if ($errors->any())
    <div class="axon-alert-error">
        <ul style="margin:0;padding-left:1.2em">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="axon-card" style="max-width:480px">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="passcode_required" value="1" id="passcode_required" {{ $settings['passcode_required'] ? 'checked' : '' }} style="accent-color:#0066FF;width:15px;height:15px">
                <label for="passcode_required" style="font-size:13px;color:#001240;cursor:pointer">パスコードを必須にする</label>
            </div>
            <div style="margin-bottom:1.25rem">
                <label class="axon-label">削除までの猶予日数</label>
                <input type="number" name="cleanup_grace_days" value="{{ old('cleanup_grace_days', $settings['cleanup_grace_days']) }}" class="axon-input" min="0" max="365" required>
            </div>
            <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="notify_before_delete" value="1" id="notify_before_delete" {{ $settings['notify_before_delete'] ? 'checked' : '' }} style="accent-color:#0066FF;width:15px;height:15px">
                <label for="notify_before_delete" style="font-size:13px;color:#001240;cursor:pointer">削除前に担当者へ通知する</label>
            </div>
            <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #D4DFF5">
                <button type="submit" class="btn-axon">保存</button>
            </div>
        </form>
    </div>
@endsection
