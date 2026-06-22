@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">ユーザー管理</h2>
    <a href="{{ route('admin.users.create') }}" class="btn-axon">+ 新規作成</a>
</div>

<div class="axon-card" style="padding:0;overflow:hidden">
    <div class="axon-table-wrap">
    <table class="axon-table">
        <thead>
            <tr>
                <th>社員コード</th>
                <th>名前</th>
                <th>部署</th>
                <th style="text-align:center">会社</th>
                <th>メールアドレス</th>
                <th style="text-align:center">権限</th>
                <th style="text-align:center">状態</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->employee_code ?? '—' }}</td>
                    <td style="font-weight:500">{{ $user->name }}</td>
                    <td>{{ $user->dept_name ?? '—' }}</td>
                    <td style="text-align:center">{{ $user->company_id ?? '—' }}</td>
                    <td>{{ $user->email }}</td>
                    <td style="text-align:center">
                        @if($user->role === 'admin')
                            <span style="background:#F0EEFF;color:#5500AA;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:500;white-space:nowrap">管理者</span>
                        @else
                            <span class="badge-dl">担当者</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        @if($user->is_active)
                            <span class="badge-recruitment" style="font-size:11px;padding:3px 8px;border-radius:20px">有効</span>
                        @else
                            <span class="badge-expired">無効</span>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-axon-outline" style="padding:4px 10px;font-size:12px">編集</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-axon-danger" style="padding:4px 10px;font-size:12px">削除</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:#7090CC;padding:2rem">ユーザーがいません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
        {{ $users->links() }}
    </div>
</div>
@endsection
