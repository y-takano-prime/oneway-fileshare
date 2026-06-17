@extends('layouts.app')

@section('content')
    <h2 class="mb-3">URL編集</h2>

    <div class="card">
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

            <form method="POST" action="{{ route('urls.update', $url) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">有効期限</label>
                    <input type="datetime-local" name="expires_at"
                        value="{{ old('expires_at', $url->expires_at->format('Y-m-d\TH:i')) }}"
                        class="form-control @error('expires_at') is-invalid @enderror">
                    @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">ダウンロード上限（空欄＝無制限）</label>
                    <input type="number" name="download_limit"
                        value="{{ old('download_limit', $url->download_limit) }}"
                        class="form-control @error('download_limit') is-invalid @enderror"
                        min="1" max="9999">
                    @error('download_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <a href="{{ route('urls.show', $url) }}" class="btn btn-outline-secondary">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
@endsection
