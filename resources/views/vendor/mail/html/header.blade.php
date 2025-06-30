@props(['url'])
<tr>
<td class="header">
<a href="{{ $url ?: 'https://zawajafrica.online/dashboard' }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel' || trim($slot) === 'ZawajAfrica' || trim($slot) === 'Zawaj Africa')
<div style="text-align: center;">
<img src="https://zawajafrica.online/images/dash.png" 
     alt="ZawajAfrica - African Muslim Dating Platform" 
     width="200" 
     height="80" 
     style="height: 80px; width: auto; max-width: 200px; display: block; margin: 0 auto; border: none;" 
     border="0">
<div class="header-subtitle" style="color: rgba(255, 255, 255, 0.9); font-size: 14px; margin-top: 8px; font-weight: 400;">
Connecting African Muslims Worldwide
</div>
</div>
@else
<div style="color: #ffffff; font-size: 28px; font-weight: 700; text-align: center;">
{!! $slot !!}
</div>
@endif
</a>
</td>
</tr>
