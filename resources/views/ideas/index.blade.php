<x-layout.layout>
    <div>
        <header class="py-8 md:py-12">
            <h1 class="text-3xl font-bold">Ideas</h1>
            <p class="text-muted-foreground text-sm mt-5">Capture your thoughts. Make a plan.</p>

            <x-card x-data @click="$dispatch('open-modal', 'create-idea')" is="button"
                class="mt-10 cursor-pointer h-32 w-full text-left">
                <div class="flex gap-1">
                    <p class="text-muted-foreground text-xl">What's the
                    <p class="text-primary font-medium text-xl">Idea?</p>
                    </p>
                </div>
            </x-card>
        </header>


        <div>
            <a href="/ideas" class="btn {{ request()->has('status') ? 'btn-outlined' : '' }}"> All</a>

            @foreach (\App\IdeaStatus::cases() as $status)
                <a href="/ideas?status={{ $status->value }}"
                    class="btn {{ request('status') === $status->value ? '' : 'btn-outlined' }}">
                    {{ $status->label() }} <span class="text-xs pl-3">{{ $statusCounts->get($status->value) }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            <div class="grid md:grid-cols-2 gap-6 items-start">
                @forelse ($ideas as $idea)
                    <x-card href="{{ route('idea.show', $idea) }}">
                        @if ($idea->image_path)
                            <div class="mb-4 -mx-4 -mt-4 rounded-t-lg overflow-hidden">
                                <img src="{{ asset('storage/' . $idea->image_path) }}" alt=""
                                    class="w-full h-auto object-cover">
                            </div>
                        @endif

                        <div class="flex gap-x-4 items-center justify-between mt-4">
                            <h3 class="text-foreground text-lg">{{ $idea->title }}</h3>
                            <div class="mt-1">
                                <x-idea.status-label status="{{ $idea->status }}">
                                    {{ $idea->status->label() }}
                                </x-idea.status-label>
                            </div>
                        </div>

                        <div class="mt-5 line-clamp-3">{{ $idea->description }}</div>
                        <div class="mt-4 text-muted-foreground font-medium">{{ $idea->created_at->diffForHumans() }}
                        </div>
                    </x-card>
                @empty
                    <x-card>
                        <p>No ideas at this time.</p>
                    </x-card>
                @endforelse
            </div>
        </div>

       <x-idea.modal />
    </div>
</x-layout.layout>
