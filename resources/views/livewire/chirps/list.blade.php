<?php

use App\Models\Chirp;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

/*
The choice of the backslash (\) as the namespace separator in PHP was largely arbitrary and based on the need for a character that wasn't already heavily used in PHP code. The backslash was one of the few characters that met this criteria. It's worth noting that this choice has been a point of some controversy in the PHP community, as the backslash is also used as an escape character in many contexts, which can lead to confusion.
*/

use Livewire\Volt\Component;

new class extends Component {
    public Collection $chirps;
    public ?Chirp $editing = null;

    public function hydrate()
    {
        $this->getChirps();
    }

    public function mount(): void
    {
        $this->getChirps();
    }

    #[On('chirp-created')]
    public function getChirps(): void
    {
        $this->chirps = Chirp::with('user')->latest()->get();
    }

    public function edit(Chirp $chirp): void
    {
        $this->editing = $chirp;

        $this->getChirps();
    }

    #[On('chirp-edit-canceled')]
    #[On('chirp-updated')]
    public function disableEditing(): void
    {
        $this->editing = null;

        $this->getChirps();
    }

    public function delete(Chirp $chirp): void
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        $this->getChirps();
    }

    /*

        NOTES

        The __() function in Laravel is a helper function used for localization or internationalization. It's used to translate the given message based on your application's current locale or return the original message if no translation exists.

        @{{ __('Edit') }} is used to display the word "Edit" in the user's preferred language, if a translation is available. If no translation is found, it will simply display "Edit".

        This is useful for applications that need to support multiple languages, as it allows you to easily translate UI elements.

        The <livewire:chirps.edit :chirp="$chirp" :key="$chirp->id" /> line in your Blade file is including the chirps.edit Livewire component and passing two properties to it: chirp and key.

        :chirp="$chirp" is passing the current $chirp object to the chirps.edit component. Inside the component, you can access this object with $this->chirp.
        :key="$chirp->id" is setting a unique key for each instance of the component. This is useful when you have multiple instances of the same component on a page and you want to maintain their individual state.
        The chirps.edit component should be defined in a file located at app/Http/Livewire/Chirps/Edit.php.

        The lines #[On('chirp-edit-canceled')] and #[On('chirp-updated')] are actually attributes in PHP 8 that are used for method annotations. They are not comments; rather, they are metadata annotations that provide additional information about the methods they precede.

        In this specific code, these annotations are used with Livewire, a Laravel library for building reactive user interfaces. Livewire uses these annotations to identify which methods should be triggered in response to specific Livewire events.

        For example:

        #[On('chirp-edit-canceled')]: This indicates that the disableEditing method should be called when the Livewire component receives a "chirp-edit-canceled" event. It likely means that some Livewire component in the application emits this event when a chirp editing is canceled.

        #[On('chirp-updated')]: This indicates that the disableEditing method should also be called when the Livewire component receives a "chirp-updated" event. It suggests that this method is intended to handle the event when a chirp is updated.

    */
}; ?>

<div wire:poll.5s class="mt-6 bg-white divide-y rounded-lg shadow-sm">
    @foreach ($chirps as $chirp)
        <div class="flex p-6 space-x-2" wire:key="{{ $chirp->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 -scale-x-100" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-gray-800">{{ $chirp->user->name }}</span>
                        <small
                            class="ml-2 text-sm text-gray-600">{{ $chirp->created_at->format('j M Y, g:i a') }}</small>
                        @unless ($chirp->created_at->eq($chirp->updated_at))
                            <small class="text-sm text-gray-600"> &middot; {{ __('edited') }}</small>
                        @endunless

                    </div>
                    @if ($chirp->user->is(auth()->user()))
                        <x-dropdown>
                            <x-slot name="trigger">
                                <button>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link wire:click="edit({{ $chirp->id }})">
                                    {{ __('Edit') }}
                                </x-dropdown-link>
                                <x-dropdown-link wire:click="delete({{ $chirp->id }})"
                                    wire:confirm="Are you sure to delete this chirp?">
                                    {{ __('Delete') }}
                                </x-dropdown-link>


                            </x-slot>
                        </x-dropdown>
                    @endif
                </div>
                @if ($chirp->is($editing))
                    <livewire:chirps.edit :chirp="$chirp" :key="$chirp->id" />
                @else
                    <p class="mt-4 text-lg text-gray-900">{{ $chirp->message }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
