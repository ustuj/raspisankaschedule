<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title></title>
<style>
*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#111;margin:16px}h1{margin:0 0 4px;font-size:24px}.subtitle{color:#555;margin-bottom:14px}.print-grid{display:grid;grid-template-columns:160px repeat({{ max(1,$groups->count()) }},1fr);border:1px solid #999}.cell{padding:7px;border-right:1px solid #bbb;border-bottom:1px solid #bbb;min-height:58px}.head{font-weight:700;background:#eee}.day{grid-column:1/-1;background:#ddd;font-weight:700;min-height:34px}.special{background:#fff8dc;font-weight:700;text-align:center}.lesson strong{display:block}.lesson small{display:block;color:#444}@page{margin:10mm}@media print{.no-print{display:none!important}body{margin:0}.print-grid{width:100%}}
</style>
</head>
<body>
<div class="no-print"><button onclick="window.print()">Печать</button></div>
<h1>РАСПИСАНИЕ НСМК</h1><div class="subtitle">{{ $selectedWeek }} неделя</div>
<div class="print-grid">
<div class="cell head">День / пара</div>
@foreach($groups as $group)<div class="cell head">{{ $group->name }}<br><small>{{ $group->speciality }}, {{ $group->course }} курс</small></div>@endforeach
@foreach($days as $day)
<div class="cell day">{{ $day }}</div>
@if($day==='Понедельник')
<div class="cell"><strong>Классный час</strong><br><small>09:00–09:50</small></div>
@foreach($groups as $group)<div class="cell special">Классный час</div>@endforeach
@endif
@foreach($pairs as $pair=>$time)
<div class="cell"><strong>{{ $pair }} пара</strong><br><small>{{ $day==='Понедельник' ? $mondayPairs[$pair] : $time }}</small></div>
@foreach($groups as $group)
@php $lesson=$lessons->first(function($item)use($group,$day,$pair,$selectedWeek){return $item->group_id===$group->id&&$item->day===$day&&(int)($selectedWeek===1?$item->week1_lesson:$item->week2_lesson)===(int)$pair;}); @endphp
<div class="cell"><span class="lesson">@if($lesson)<strong>{{ $lesson->subject->short_name ?: $lesson->subject->name }}</strong><small>{{ $lesson->teacher->name }}</small><small>{{ $lesson->classroom?->number ?? $lesson->classroom?->name ?? '—' }}</small>@endif</span></div>
@endforeach
@endforeach
@endforeach
</div>
<script>document.title='';</script>
</body></html>