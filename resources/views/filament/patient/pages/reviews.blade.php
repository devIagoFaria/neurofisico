<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($doctors as $doctor)
            @php
                $review = $doctor->reviews->first();
                $avgRating = round($doctor->reviews->avg('rating'), 1);
            @endphp

            <x-filament::section class="relative overflow-hidden">

                @if ($avgRating > 0)
                    <div class="absolute right-4 top-0 rounded-bl-xl rounded-br-xl bg-[#5a67f2] px-3 py-2 shadow-sm">
                        <div class="flex items-center gap-0.5">
                            @foreach (range(1, 5) as $i)
                                @if ($avgRating >= $i)
                                    <x-heroicon-s-star class="h-4 w-4 text-yellow-400" />
                                @elseif ($avgRating >= $i - 0.5)
                                    <x-heroicon-s-star class="h-4 w-4 text-yellow-400 opacity-60" />
                                @else
                                    <x-heroicon-o-star class="h-4 w-4 text-white/50" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-col items-center">
                    <div class="mb-4">
                        <img src="{{ $doctor->user->profile_image ?? asset('images/avatar.png') }}"
                            class="h-24 w-24 rounded-full object-cover shadow-md ring-4 ring-gray-50 dark:ring-gray-800">
                    </div>

                    <h3 class="text-center text-lg font-bold text-gray-950 dark:text-white">
                        {{ $doctor->user->full_name }}
                    </h3>

                    <p class="mt-1 text-center text-xs font-medium text-gray-400 tracking-wider">
                        {{ $doctor->specializations->pluck('name')->join(', ') }}
                    </p>

                    @if ($review)
                        <p
                            class="mt-6 px-4 text-center text-sm font-semibold text-gray-800 dark:text-gray-200 line-clamp-2">
                            {{ $review->review }}
                        </p>
                    @endif

                    @if ($review)
                        <div class="mt-2 flex justify-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star
                                    class="h-6 w-6 {{ $i <= ($review->rating ?? 0) ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-700' }}" />
                            @endfor
                        </div>
                    @endif

                    <div class="mt-2 w-full flex justify-center">
                        @if ($review)
                            {{ ($this->editReview)(['review_id' => $review->id]) }}
                        @else
                            {{ ($this->createReview)(['doctor_id' => $doctor->id]) }}
                        @endif
                    </div>
                </div>
            </x-filament::section>

        @empty
            <div class="col-span-full">
                <x-filament::section>
                    <p class="text-center text-gray-500">{{ __('messages.review.no_doctors_available_to_give_rating') }}</p>
                </x-filament::section>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
