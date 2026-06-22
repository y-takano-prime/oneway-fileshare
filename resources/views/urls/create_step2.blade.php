@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">新規作成</h2>

{{-- ステップバー --}}
<div class="axon-steps">
    <div class="axon-step">1. 送付先設定</div>
    <div class="axon-step active">2. ファイル選択</div>
    <div class="axon-step">3. メール確認</div>
</div>

@if($errors->any())
<div class="axon-alert-error">
    <ul style="margin:0;padding-left:1.2em">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('urls.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- アップロードエリア --}}
    <div class="axon-card" style="margin-bottom:1rem">
        <div style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;font-weight:600;margin-bottom:10px">新規ファイルをアップロード</div>
        <div id="upload-area" style="border:1.5px dashed #B8CCF0;border-radius:8px;padding:2.5rem;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:background .15s" onclick="document.getElementById('file-input').click()">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7090CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
            <div style="font-size:13px;color:#7090CC">クリックまたはドラッグ＆ドロップでアップロード</div>
            <div style="margin-top:6px;font-size:12px;color:#7090CC">対応形式：JPG, PNG, GIF, PDF, Word, Excel, PowerPoint, ZIP, CSV, TXT（1ファイルにつき最大100MB）</div>
            <div id="file-name" style="margin-top:8px;font-size:13px;color:#0066FF;font-weight:500"></div>
        </div>
        <div style="margin-top:10px;background:#E6F0FF;border:0.5px solid #B0CCFF;color:#0044CC;border-radius:6px;padding:10px 14px;font-size:13px">
            <strong>複数ファイルを送付する場合：</strong>
            フォルダにまとめてZIPに圧縮してからアップロードしてください。1つのURLで相手先にまとめて届けられます。
        </div>
        <input type="file" id="file-input" name="upload_file" class="d-none">
    </div>

    {{-- アップロード済みから選択 --}}
    <div class="axon-card" style="padding:0;overflow:hidden;margin-bottom:1rem">
        <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF;font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;font-weight:600">アップロード済みから選択</div>
        <div class="axon-table-wrap">
        <table class="axon-table">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th>ファイル名</th>
                    <th>サイズ</th>
                    <th>アップロード日</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr style="cursor:pointer" onclick="this.querySelector('input').click()">
                    <td><input type="radio" name="shared_file_id" value="{{ $file->id }}" style="accent-color:#0066FF;width:15px;height:15px" {{ (int) $preselectedFileId === $file->id ? 'checked' : '' }}></td>
                    <td style="font-weight:500">
                        {{ $file->original_name }}
                        @if($file->category === 'business')
                            <span class="badge-business">取引先</span>
                        @elseif($file->category === 'recruitment')
                            <span class="badge-recruitment">採用</span>
                        @endif
                    </td>
                    <td style="color:#001240;font-size:12px">{{ round($file->file_size / 1024, 1) }} KB</td>
                    <td style="color:#001240;font-size:12px">{{ $file->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:#7090CC;padding:1.5rem">アップロード済みのファイルがありません</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between">
        <a href="{{ route('urls.create') }}" class="btn-axon-ghost">← 戻る</a>
        <button type="submit" class="btn-axon">URLを発行する →</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
const uploadArea = document.getElementById('upload-area');
const fileInput  = document.getElementById('file-input');
const fileName   = document.getElementById('file-name');

function clearRadioSelection() {
    document.querySelectorAll('input[name="shared_file_id"]').forEach(r => r.checked = false);
}
function clearUploadSelection() {
    fileInput.value = '';
    fileName.textContent = '';
}

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
        fileName.textContent = fileInput.files[0].name;
        clearRadioSelection();
    }
});
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.background = '#EEF4FF'; });
uploadArea.addEventListener('dragleave', () => { uploadArea.style.background = ''; });
uploadArea.addEventListener('drop', e => {
    e.preventDefault(); uploadArea.style.background = '';
    if (e.dataTransfer.files.length) {
        const dt = e.dataTransfer;
        fileInput.files = dt.files;
        fileName.textContent = dt.files[0].name;
        clearRadioSelection();
    }
});

// ラジオボタン：選択済みを再クリックで解除、新規アップロード選択は解除
document.querySelectorAll('input[name="shared_file_id"]').forEach(radio => {
    radio.addEventListener('mousedown', function() {
        this._wasChecked = this.checked;
    });
    radio.addEventListener('click', function() {
        if (this._wasChecked) {
            this.checked = false;
        } else {
            clearUploadSelection();
        }
    });
});
</script>
@endsection
