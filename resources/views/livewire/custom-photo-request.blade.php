<div>
    @if ($isOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/40 backdrop-blur-xs"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="photoRequestTitle"
        >
            <div class="fixed inset-0" wire:click="close"></div>

            <div class="relative w-full max-w-lg rounded-2xl bg-surface-container-lowest p-6 sm:p-8 shadow-2xl z-10 max-h-[90vh] overflow-y-auto">
                <button
                    type="button"
                    wire:click="close"
                    class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors"
                    aria-label="Close"
                >
                    <x-ui.icon name="close" class="text-[22px]" />
                </button>

                @if ($submitted)
                    <div class="py-6 text-center space-y-4">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-secondary-fixed text-secondary">
                            <x-ui.icon name="check_circle" class="text-[32px]" />
                        </div>
                        <h3 class="text-headline-md font-bold text-on-surface">Photos Received!</h3>
                        <p class="text-body-md text-on-surface-variant">Our sourcing desk will review your photos and contact you on <strong class="text-on-surface">{{ $phone }}</strong> shortly.</p>
                        <button type="button" wire:click="close" class="min-h-11 px-5 py-2 rounded-lg bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high transition-colors">
                            Done
                        </button>
                    </div>
                @else
                    <div class="space-y-5">
                        <div>
                            <span class="text-label-sm font-bold uppercase tracking-wider text-primary">Custom Photo Request</span>
                            <h3 class="text-headline-sm font-bold text-on-surface mt-1" id="photoRequestTitle">Send Your Reference Photos</h3>
                            <p class="text-body-sm text-on-surface-variant mt-1">Upload photos of styles you want sourced. We'll find them in Aba and send you a quote.</p>
                        </div>

                        <form wire:submit="submit" class="space-y-4" novalidate>
                            <div>
                                <label for="photo-phone" class="block text-label-md font-semibold text-on-surface mb-1.5">Your WhatsApp Number</label>
                                <input
                                    id="photo-phone"
                                    type="tel"
                                    wire:model="phone"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    placeholder="+234 801 234 5678"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-4 py-3 text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary"
                                    required
                                />
                                @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            {{-- Photo upload area --}}
                            <div>
                                <label class="block text-label-md font-semibold text-on-surface mb-1.5">
                                    Reference Photos <span class="text-on-surface-variant font-normal">(up to 5, max 5MB each)</span>
                                </label>

                                <label
                                    for="photo-upload"
                                    class="flex flex-col items-center justify-center gap-2 w-full rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-low px-4 py-6 cursor-pointer hover:bg-surface-container transition-colors"
                                >
                                    <x-ui.icon name="add_photo_alternate" class="text-[36px] text-primary" />
                                    <span class="text-label-md font-semibold text-on-surface">Click to add photos</span>
                                    <span class="text-body-sm text-on-surface-variant">JPG, PNG, WEBP</span>
                                    <input
                                        id="photo-upload"
                                        type="file"
                                        wire:model="photos"
                                        multiple
                                        accept="image/*"
                                        class="sr-only"
                                    />
                                </label>

                                @error('photos') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                                @error('photos.*') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror

                                {{-- Previews --}}
                                @if (count($photos))
                                    <div class="mt-3 grid grid-cols-3 gap-2" wire:loading.remove wire:target="photos">
                                        @foreach ($photos as $i => $photo)
                                            <div class="relative group rounded-lg overflow-hidden aspect-square bg-surface-container">
                                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview {{ $i + 1 }}" />
                                                <button
                                                    type="button"
                                                    wire:click="removePhoto({{ $i }})"
                                                    class="absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-error text-on-error opacity-0 group-hover:opacity-100 transition-opacity"
                                                    aria-label="Remove photo"
                                                >
                                                    <x-ui.icon name="close" class="text-[14px]" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div wire:loading wire:target="photos" class="mt-2 text-body-sm text-on-surface-variant flex items-center gap-2">
                                    <x-ui.icon name="hourglass_top" class="text-[16px] animate-spin" /> Uploading...
                                </div>
                            </div>

                            <div>
                                <label for="photo-notes" class="block text-label-md font-semibold text-on-surface mb-1.5">Country</label>
                                <select
                                    id="photo-country"
                                    wire:model="country"
                                    class="w-full min-h-11 rounded-lg border-0 bg-surface-container-low px-4 py-3 text-body-md text-on-surface focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer"
                                >
                                    @foreach (config('abatradehub.countries', ['Nigeria', 'Ghana', 'Benin Republic', "Cote d'Ivoire", 'Sierra Leone', 'Other African Country']) as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('country') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="photo-notes" class="block text-label-md font-semibold text-on-surface mb-1.5">Additional Notes <span class="text-on-surface-variant font-normal">(optional)</span></label>
                                <textarea
                                    id="photo-notes"
                                    wire:model="notes"
                                    rows="2"
                                    placeholder="e.g. Need 20 pieces, sizes M-XL, prefer black and brown"
                                    class="w-full rounded-lg border-0 bg-surface-container-low px-4 py-3 text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                                ></textarea>
                                @error('notes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            {{-- Honeypot --}}
                            <div class="hidden" aria-hidden="true">
                                <input type="text" wire:model="website" tabindex="-1" autocomplete="off" />
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-1">
                                <button type="button" wire:click="close" class="min-h-11 px-4 py-2 rounded-lg bg-surface-container text-on-surface font-semibold hover:bg-surface-container-high transition-colors">
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="min-h-11 px-5 py-2 rounded-lg bg-primary-container text-on-primary-container font-bold hover:bg-primary transition-colors shadow-sm disabled:opacity-70 flex items-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="submit">Send Photos</span>
                                    <span wire:loading wire:target="submit">Sending...</span>
                                    <x-ui.icon name="send" class="text-[18px]" wire:loading.remove wire:target="submit" />
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
