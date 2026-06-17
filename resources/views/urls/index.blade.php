@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">URL管理</h2>
        @if (Auth::user()->role !== 'admin')
            <a href="{{ route('urls.create') }}" class="btn btn-primary">新規URL発行</a>
        @endif
    </div>

    <div class="card">
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('urls.index') }}" class="mb-3">
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="相手先名・メール・ファイル名で検索">
                    <button type="submit" class="btn btn-outline-secondary">検索</button>
                    @if(request('q'))
                        <a href="{{ route('urls.index') }}" class="btn btn-outline-danger">クリア</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>相手先</th>
                        <th>発行日時</th>
                        <th>有効期限</th>
                        <th>DL状況</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($urls as $url)
                        <tr class="{{ $url->expires_at->isPast() ? 'text-muted' : '' }}">
                            <td><a href="{{ route('urls.show', $url) }}">{{ $url->sharedFile->original_name ?? '-' }}</a></td>
                            <td>
                                {{ $url->recipient_name ?: $url->recipient_email }}
                                <div class="text-muted small">{{ $url->recipient_email }}</div>
                            </td>
                            <td>{{ $url->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                {{ $url->expires_at->format('Y-m-d H:i') }}
                                @if ($url->expires_at->isPast())
                                    <span class="badge bg-secondary-lt ms-1">期限切れ</span>
                                @endif
                            </td>
                            <td>
                                @if ($url->download_count > 0)
                                    <span class="badge bg-success-lt">済</span>
                                @else
                                    <span class="badge bg-secondary-lt">未</span>
                                @endif
                                <span class="ms-1 small">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('urls.destroy', $url) }}" onsubmit="return confirm('無効化しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">無効化</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">URLがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
