@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">ユーザー管理</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">新規作成</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>権限</th>
                        <th>状態</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-purple-lt' : 'bg-blue-lt' }}">{{ $user->role === 'admin' ? '管理者' : '担当者' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $user->is_active ? '有効' : '無効' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">編集</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">ユーザーがいません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
