@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === 'Zawaj Africa')
<img src="{{ config('app.url') }}/images/dash.png" alt="Zawaj Africa" width="200" height="75" style="height: 75px; width: auto; max-width: 200px; display: block; margin: 0 auto; border: none;" border="0">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
