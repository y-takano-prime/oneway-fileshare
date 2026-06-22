@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">ユーザー作成</h2>

<div class="axon-card">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @if ($errors->any())
        <div class="axon-alert-error">
            <ul style="margin:0;padding-left:1.2em">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif
        <div style="margin-bottom:1rem">
            <label class="axon-label">名前</label>
            <input type="text" name="name" value="{{ old('name') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">社員コード</label>
            <input type="text" name="employee_code" value="{{ old('employee_code') }}" class="axon-input" maxlength="50">
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">パスワード</label>
            <input type="password" name="password" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">パスワード（確認）</label>
            <input type="password" name="password_confirmation" class="axon-input" required>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">権限</label>
            <select name="role" class="axon-input" required>
                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>担当者</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>管理者</option>
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">会社ID</label>
            <select name="company_id" class="axon-input">
                <option value="">未設定</option>
                @foreach(['P', 'M', 'T', 'H'] as $c)
                <option value="{{ $c }}" {{ old('company_id') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1rem">
            <label class="axon-label">部署</label>
            <select name="dept_id" class="axon-input">
                <option value="">未設定</option>
                @foreach($depts as $dept)
                <option value="{{ $dept->id }}" {{ old('dept_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:8px">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} style="accent-color:#0066FF;width:15px;height:15px">
            <label for="is_active" style="font-size:13px;color:#001240;cursor:pointer">有効にする</label>
        </div>
        <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #D4DFF5">
            <button type="submit" class="btn-axon">作成</button>
        </div>
    </form>
</div>
@endsection
