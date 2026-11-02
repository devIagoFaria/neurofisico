@props([
    'getState',
    'getAvatar' => null,
    'getPrimary' => null,
    'getSecondary' => null,
    'getPrimaryUrl' => null,
    'shouldOpenPrimaryUrlInNewTab' => null,
    'getSecondaryUrl' => null,
    'shouldOpenSecondaryUrlInNewTab' => null,
    'getAvatarShape' => null,
    'getAvatarSize' => null,
    'getUrl' => null,
    'shouldOpenUrlInNewTab' => null,
    'getVisualSize' => null,
    'getColor' => null,
    'formatState' => null,
])

@php
    $visualSize = $getVisualSize();
    $sizes = [
        'sm' => ['primary' => '.875rem', 'secondary' => '.75rem'],
        'md' => ['primary' => '1rem', 'secondary' => '.75rem'],
        'lg' => ['primary' => '1.125rem', 'secondary' => '.875rem'],
    ];
    $primaryFs = $sizes[$visualSize]['primary'] ?? '1rem';
    $secondaryFs = $sizes[$visualSize]['secondary'] ?? '.75rem';
    $sizeClass = 'fi-identity--' . $visualSize;

    // Get color if set (pass state to getColor method)
    $state = $getState();
    $color = $getColor ? $getColor($state) : null;
@endphp

<div class="fi-identity {{ $sizeClass }}"
    style="--fi-identity-primary-size: {{ $primaryFs }}; --fi-identity-secondary-size: {{ $secondaryFs }};">
    @if ($getAvatar && filled($getAvatar()))
        @php
            $shape = $getAvatarShape();
            $size = $getAvatarSize();
        @endphp
        <img src="{{ $getAvatar() }}" alt="" class="fi-identity__avatar fi-identity__avatar--{{ $shape }}"
            style="min-width: {{ $size }}; min-height: {{ $size }}; width: {{ $size }}; height: {{ $size }};"
            loading="lazy" />
    @endif

    <div class="fi-identity__text">
        @php
            $primaryText = $getPrimary ? $getPrimary() ?? $getState() : $getState();
            $wrapperUrl = $getUrl ? $getUrl() : null;

            // Format the primary text using the parent's formatState method if available
            if ($formatState && is_callable($formatState)) {
                $primaryText = $formatState($primaryText);
            }
        @endphp

        @if (!$wrapperUrl && filled($getPrimaryUrl()))
           <a href="{{ $getPrimaryUrl() }}"
            class="fi-identity__primary fi-identity__primary--link"
            @if ($color) style="color: {{ $color }}; text-decoration: none; transition: color 0.3s;" @endif
            @if ($shouldOpenPrimaryUrlInNewTab && $shouldOpenPrimaryUrlInNewTab()) target="_blank" rel="noopener noreferrer" @endif
            onmouseover="this.style.color='#007bff';"
            onmouseout="this.style.color='{{ $color ?? 'inherit' }}';">

                @if ($primaryText instanceof \Illuminate\Contracts\Support\Htmlable)
                    {!! $primaryText !!}
                @else
                    {{ $primaryText }}
                @endif
            </a>

        @else
            <span class="fi-identity__primary"
                @if ($color) style="color: {{ $color }};" @endif>
                @if ($primaryText instanceof \Illuminate\Contracts\Support\Htmlable)
                    {!! $primaryText !!}
                @else
                    {{ $primaryText }}
                @endif
            </span>
        @endif

        @if ($getSecondary && filled($getSecondary()))
            @php
                $secondaryText = $getSecondary();

                // Format the secondary text using the parent's formatState method if available
                if ($formatState && is_callable($formatState)) {
                    $secondaryText = $formatState($secondaryText);
                }
            @endphp
            @if (!$wrapperUrl && filled($getSecondaryUrl()))
                <a href="{{ $getSecondaryUrl() }}" class="fi-identity__secondary fi-identity__secondary--link"
                    @if ($shouldOpenSecondaryUrlInNewTab && $shouldOpenSecondaryUrlInNewTab()) target="_blank" rel="noopener noreferrer" @endif>
                    @if ($secondaryText instanceof \Illuminate\Contracts\Support\Htmlable)
                        {!! $secondaryText !!}
                    @else
                        {{ $secondaryText }}
                    @endif
                </a>
            @else
                <span class="fi-identity__secondary">
                    @if ($secondaryText instanceof \Illuminate\Contracts\Support\Htmlable)
                        {!! $secondaryText !!}
                    @else
                        {{ $secondaryText }}
                    @endif
                </span>
            @endif
        @endif
    </div>
</div>
