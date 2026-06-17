@extends('layouts.app')

@section('content')
    <h2 class="mb-3">ユーザー編集</h2>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
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
                    <label class="form-label">名前</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">社員コード</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $user->employee_code) }}" class="form-control" maxlength="50">
                </div>
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">パスワード（変更する場合のみ入力）</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">パスワード（確認）</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">権限</label>
                    <select name="role" class="form-select" required>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>担当者</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>管理者</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">会社ID</label>
                    <select name="company_id" class="form-select">
                        <option value="">未設定</option>
                        @foreach(['P', 'M', 'T', 'H'] as $c)
                        <option value="{{ $c }}" {{ old('company_id', $user->company_id) === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">部署</label>
                    <select name="dept_id" class="form-select">
                        <option value="">未設定</option>
                        @foreach($depts as $dept)
                        <option value="{{ $dept->id }}" {{ old('dept_id', $user->dept_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">有効にする</label>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">更新</button>
            </div>
        </form>
    </div>
@endsection
