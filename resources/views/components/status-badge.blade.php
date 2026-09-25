@props(['status'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold '.$status->color()]) }}>
    {{ $status->label() }}
</span>
