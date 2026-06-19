ストレージ占有率が警告しきい値を超えました

担当者「{{ $targetUser->name }}」（{{ $targetUser->email }}）のアップロードファイル使用量が、
全体ストレージ容量に対して大きな割合を占めています。

@php
    $usedDisplay = $usedMb >= 1024 ? round($usedMb / 1024, 1) . ' GB' : $usedMb . ' MB';
@endphp
使用量: {{ $usedDisplay }} / {{ round($capMb / 1024, 1) }} GB（{{ $percent }}%）

管理画面の「ストレージ」からユーザー別内訳をご確認ください。
