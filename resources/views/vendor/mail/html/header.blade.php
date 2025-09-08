@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://raw.githubusercontent.com/fatur04/e-tiket/main/seoa.png" class="logo" alt="HR SEOA">
<!-- <img src="{{ asset('images/seoa.png') }}" class="logo" alt="HR SEOA"> -->
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>