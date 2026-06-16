@extends('layouts.app')

@section('content')
    <h2 class="mb-3">URL発行</h2>

    <div class="card">
        <form method="POST" action="{{ route('urls.store') }}">
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
                <div class="mb-3">
                    <label class="form-label">ファイル</label>
                    <select name="shared_file_id" class="form-select" required>
                        <option value="">選択してください</option>
                        @foreach ($files as $file)
                            <option value="{{ $file->id }}" {{ old('shared_file_id') == $file->id ? 'selected' : '' }}>{{ $file->original_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">相手先メールアドレス</label>
                    <input type="email" name="recipient_email" value="{{ old('recipient_email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">有効期限</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">パスコード（任意）</label>
                    <input type="text" name="passcode" value="{{ old('passcode') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">ダウンロード回数上限（任意）</label>
                    <input type="number" name="download_limit" value="{{ old('download_limit') }}" class="form-control" min="1" max="9999">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="notify_on_download" value="1" class="form-check-input" id="notify_on_download" {{ old('notify_on_download') ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_on_download">ダウンロード時に通知する</label>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">発行</button>
            </div>
        </form>
    </div>
@endsection
