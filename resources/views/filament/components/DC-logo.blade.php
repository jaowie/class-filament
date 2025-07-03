@if(request()->routeIs('filament.admin.login'))
    {{-- Large logo for login page --}}
     <img src="{{ asset('images/DC-Logo.png') }}" class="h-18 w-18">
    <img src="{{ asset('images/DC-Logo2.png') }}" class="h-18 w-18">
@else
    {{-- Small logo for top bar --}}
    <img src="{{ asset('images/DC-Logo.png') }}" class="h-18 w-18">
    <img src="{{ asset('images/DC-Logo2.png') }}" class="h-12">
@endif