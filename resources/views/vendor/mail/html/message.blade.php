<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="'https://zawajafrica.online/dashboard'">
ZawajAfrica
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} ZawajAfrica. {{ __('All rights reserved.') }}
<br>
<small style="color: #a0aec0;">
{{ __('Connecting African Muslims worldwide with halal relationships.') }}
</small>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
