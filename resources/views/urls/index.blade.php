@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">URL管理</h2>
        <a href="{{ route('urls.create') }}" class="btn btn-primary">新規URL発行</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>相手先</th>
                        <th>有効期限</th>
                        <th>DL数</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($urls as $url)
                        <tr>
                            <td><a href="{{ route('urls.show', $url) }}">{{ $url->sharedFile->original_name ?? '-' }}</a></td>
                            <td>{{ $url->recipient_email }}</td>
                            <td>{{ $url->expires_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
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
                            <td colspan="5" class="text-center text-muted">URLがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
