@extends('layouts.app')

@section('content')
    <h2 class="mb-3">ファイル管理</h2>

    <div class="card mb-4">
        <div class="card-body">
            <div id="drop-area" class="border border-dashed rounded p-5 text-center" style="cursor:pointer;">
                <p class="mb-2">ここにファイルをドラッグ＆ドロップ、またはクリックして選択</p>
                <input type="file" id="file-input" multiple class="d-none">
            </div>
            <div id="progress-wrapper" class="mt-3 d-none">
                <div class="progress">
                    <div id="progress-bar" class="progress-bar" style="width:0%"></div>
                </div>
            </div>
            <div id="upload-message" class="mt-2"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">アップロード済みファイル</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>サイズ</th>
                        <th>アップロード日時</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $file)
                        <tr>
                            <td>{{ $file->original_name }}</td>
                            <td>{{ number_format($file->file_size / 1024, 1) }} KB</td>
                            <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                @if (Auth::user()->role !== 'admin')
                                    <a href="{{ route('urls.create', ['shared_file_id' => $file->id]) }}" class="btn btn-sm btn-primary">URL発行</a>
                                @endif
                                <form method="POST" action="{{ route('files.destroy', $file) }}" class="d-inline" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">削除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">ファイルがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const progressWrapper = document.getElementById('progress-wrapper');
        const progressBar = document.getElementById('progress-bar');
        const uploadMessage = document.getElementById('upload-message');

        dropArea.addEventListener('click', () => fileInput.click());

        ['dragover', 'dragleave', 'drop'].forEach((eventName) => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        dropArea.addEventListener('drop', (e) => {
            if (e.dataTransfer.files.length) {
                uploadFiles(e.dataTransfer.files);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                uploadFiles(fileInput.files);
            }
        });

        function uploadFiles(fileList) {
            const formData = new FormData();
            for (const file of fileList) {
                formData.append('files[]', file);
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('files.store') }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            progressWrapper.classList.remove('d-none');
            progressBar.style.width = '0%';
            uploadMessage.textContent = '';

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                }
            };

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    window.location.reload();
                } else {
                    uploadMessage.textContent = 'アップロードに失敗しました';
                }
            };

            xhr.onerror = () => {
                uploadMessage.textContent = 'アップロードに失敗しました';
            };

            xhr.send(formData);
        }
    </script>
@endsection
