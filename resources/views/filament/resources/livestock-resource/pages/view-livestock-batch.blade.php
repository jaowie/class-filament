<x-filament::page>

<h1 class="text-xl font-bold mb-4">
    {{ Str::upper($this->owner->full_name) }} <br> {{ Str::upper($this->batch) }} <br> {{ Str::upper($this->livestocks[0]->type) }}
</h1>

    {{ $this->table }}
</x-filament::page>
    @php
    use Illuminate\Support\Str;
@endphp