@props(['slot', 'className' => '', 'format' => 'auto', 'style' => 'display:block'])

@php
    $publisherId = config('ads.publisher_id');
    $slotId = config('ads.slots.' . $slot, '');
@endphp

@if($publisherId && $slotId)
    <div class="ad-container flex justify-center overflow-hidden {{ $className }}"
         style="min-height: 50px;">
        <ins class="adsbygoogle"
             style="{{ $style }}"
             data-ad-client="ca-pub-{{ $publisherId }}"
             data-ad-slot="{{ $slotId }}"
             data-ad-format="{{ $format }}"
             data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
@endif
