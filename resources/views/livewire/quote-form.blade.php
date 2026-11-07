@php
    $input = 'block w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-4 py-3 text-body-md text-on-surface placeholder:text-on-surface-variant/60 transition-all focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary';
@endphp

<div>
    @if ($submitted)
        <div class="flex items-start gap-3 rounded-lg bg-secondary-fixed p-4 text-label-md text-on-secondary-fixed-variant" role="status">
            <x-ui.icon name="check_circle" class="shrink-0 text-[20px]" />
            <div class="space-y-2">
                <p>Thank you! Our sourcing desk has received your request and will contact you on WhatsApp shortly.</p>
                <button type="button" wire:click="$set('submitted', false)" class="min-h-11 text-label-md font-bold underline">Send another request</button>
            </div>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <div>
                <label for="quote-name" class="mb-1.5 block text-label-md font-semibold text-on-surface">Your Name / Business Name</label>
                <input id="quote-name" type="text" wire:model="name" autocomplete="name" placeholder="e.g. Amaka / Luxe Fashion Hub" class="{{ $input }}" required>
                @error('name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quote-phone" class="mb-1.5 block text-label-md font-semibold text-on-surface">WhatsApp Number</label>
                <input id="quote-phone" type="tel" wire:model="phone" autocomplete="tel" inputmode="tel" placeholder="e.g. +234 801 234 5678" class="{{ $input }}" required>
                @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quote-country" class="mb-1.5 block text-label-md font-semibold text-on-surface">Country</label>
                <select id="quote-country" wire:model="country" class="{{ $input }} cursor-pointer" required>
                    @foreach (config('abatradehub.countries', ['Nigeria', 'Ghana', 'Benin Republic', "Cote d'Ivoire", 'Sierra Leone', 'Other African Country']) as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                @error('country') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quote-message" class="mb-1.5 block text-label-md font-semibold text-on-surface">What items do you need?</label>
                <textarea id="quote-message" wire:model="message" rows="3" placeholder="e.g. 20 pairs of sandals, 15 leather bags..." class="{{ $input }} resize-none" required></textarea>
                @error('message') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label for="quote-website">Website</label>
                <input id="quote-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex min-h-12 w-full items-center justify-center gap-2 rounded-lg bg-primary-container px-6 py-4 text-label-lg font-bold text-on-primary-container shadow-md transition-all hover:bg-primary active:scale-[0.99] disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="submit">Submit Quote Request</span>
                <span wire:loading wire:target="submit">Sending...</span>
                <x-ui.icon name="send" class="text-[18px]" wire:loading.remove wire:target="submit" />
            </button>
        </form>

        <p class="mt-4 text-center text-body-sm text-on-surface-variant">We never share your contact details.</p>
    @endif
</div>

