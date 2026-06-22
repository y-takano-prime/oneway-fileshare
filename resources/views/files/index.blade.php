@extends('layouts.app')

@section('content')
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">ファイル管理</h2>

    <div class="axon-card" style="margin-bottom:1rem">
        <div id="drop-area" style="border:1px dashed #B8CCF0;border-radius:8px;padding:2.5rem;text-align:center;cursor:pointer">
            <p style="margin:0;color:#001240;font-size:13px">ここにファイルをドラッグ＆ドロップ、またはクリックして選択</p>
            <p style="margin:8px 0 0;color:#7090CC;font-size:12px">対応形式：JPG, PNG, GIF, PDF, Word, Excel, PowerPoint, ZIP, CSV, TXT（1ファイルにつき最大100MB）</p>
            <input type="file" id="file-input" multiple style="display:none">
        </div>
        <div style="margin-top:1rem;background:#E6F0FF;border:0.5px solid #B0CCFF;color:#0044CC;border-radius:6px;padding:10px 14px;font-size:13px">
            <strong>複数ファイルを送付する場合：</strong>
            まとめてZIPに圧縮してからアップロードしてください。1つのURLで相手先にまとめて届けられます。
        </div>
        <div id="progress-wrapper" style="margin-top:1rem;display:none">
            <div class="axon-bar">
                <div id="progress-bar" class="axon-bar-fill" style="width:0%"></div>
            </div>
        </div>
        <div id="upload-message" style="margin-top:8px;font-size:13px;color:#CC0000"></div>
    </div>

    <div class="axon-card" style="padding:0;overflow:hidden">
        <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF">
            <span style="font-size:13px;font-weight:500;color:#001240">アップロード済みファイル</span>
        </div>
        <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF">
            <form method="GET" action="{{ route('files.index') }}" style="display:flex;gap:8px;max-width:420px">
                <input type="text" name="q" value="{{ request('q') }}" class="axon-input" placeholder="ファイル名で検索" style="flex:1">
                <button type="submit" class="btn-axon-outline" style="white-space:nowrap">検索</button>
                @if(request('q'))
                    <a href="{{ route('files.index') }}" class="btn-axon-ghost" style="white-space:nowrap">クリア</a>
                @endif
            </form>
        </div>
            <div class="axon-table-wrap">
            <table class="axon-table">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>URL数</th>
                        @if (Auth::user()->role === 'admin')
                            <th>アップロード者</th>
                        @endif
                        <th>サイズ</th>
                        <th>アップロード日時</th>
                        <th>削除予定日</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $file)
                        <tr>
                            <td style="font-weight:500">{{ $file->original_name }}</td>
                            <td>
                                {{ $file->download_urls_count }}件
                                @if (Auth::user()->role !== 'admin' && $file->downloadUrls->isNotEmpty())
                                    @foreach ($file->downloadUrls as $u)
                                        <div style="color:#7090CC;font-size:11px;margin-top:2px">{{ $u->recipient_name ?: $u->recipient_email }}</div>
                                    @endforeach
                                @endif
                            </td>
                            @if (Auth::user()->role === 'admin')
                                <td>{{ optional($file->user)->name ?? '-' }}</td>
                            @endif
                            <td style="color:#001240;font-size:12px">{{ number_format($file->file_size / 1024, 1) }} KB</td>
                            <td style="color:#001240;font-size:12px">{{ $file->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @php
                                    $earliestUrl = $file->downloadUrls->sortBy('expires_at')->first();
                                    $deletionDate = $earliestUrl ? $earliestUrl->expires_at->copy()->addDays($graceDays) : null;
                                @endphp
                                @if ($deletionDate)
                                    <span style="font-size:12px">{{ $deletionDate->format('Y-m-d') }}</span>
                                    @if ($deletionDate->isPast())
                                        <span class="badge-expired" style="margin-left:4px">削除済</span>
                                    @elseif ($deletionDate->diffInDays(now()) <= 3)
                                        <span class="badge-wait" style="margin-left:4px">まもなく</span>
                                    @endif
                                @else
                                    <span style="color:#B0C0E0;font-size:12px">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                @if (Auth::user()->role !== 'admin')
                                    <a href="{{ route('urls.create', ['shared_file_id' => $file->id]) }}" class="btn-axon" style="padding:4px 10px;font-size:12px">URL発行</a>
                                @endif
                                <form method="POST" action="{{ route('files.destroy', $file) }}" style="display:inline" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-axon-danger" style="padding:4px 10px;font-size:12px">削除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role === 'admin' ? 7 : 6 }}" style="text-align:center;color:#7090CC;padding:2rem">ファイルがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        <div style="padding:12px 16px;border-top:0.5px solid #D0DEFF">
            {{ $files->links() }}
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

            progressWrapper.style.display = 'block';
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
