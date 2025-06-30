<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="'https://zawajafrica.online/dashboard'">
            ZawajAfrica
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} ZawajAfrica. @lang('All rights reserved.')
            
            Connecting African Muslims worldwide with halal relationships.
            
            Visit us: https://zawajafrica.online
            Support: support@zawajafrica.online
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
