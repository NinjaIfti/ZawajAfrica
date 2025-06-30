@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" 
   class="button button-{{ $color }}" 
   target="_blank" 
   rel="noopener"
   style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); 
          border-radius: 8px; 
          color: #ffffff; 
          display: inline-block; 
          font-family: 'Segoe UI', Arial, sans-serif; 
          font-size: 16px; 
          font-weight: 600; 
          line-height: 1.5; 
          text-align: center; 
          text-decoration: none; 
          padding: 16px 32px; 
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          transition: all 0.3s ease;">
{!! $slot !!}
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
