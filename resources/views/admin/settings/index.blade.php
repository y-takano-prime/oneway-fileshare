@extends('layouts.app')

@section('content')
    <h2 class="mb-3">システム設定</h2>

    <div class="card">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-3 form-check">
                    <input type="checkbox" name="passcode_required" value="1" class="form-check-input" id="passcode_required" {{ $settings['passcode_required'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="passcode_required">パスコードを必須にする</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">削除までの猶予日数</label>
                    <input type="number" name="cleanup_grace_days" value="{{ old('cleanup_grace_days', $settings['cleanup_grace_days']) }}" class="form-control" min="0" max="365" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="notify_before_delete" value="1" class="form-check-input" id="notify_before_delete" {{ $settings['notify_before_delete'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_before_delete">削除前に担当者へ通知する</label>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </form>
    </div>
@endsection
