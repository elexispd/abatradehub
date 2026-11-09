<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error &bull; {{ config('abatradehub.name', 'AbaTradeHub') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0..1,0&display=block">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#9f3e07',
                        secondary: '#2c694e',
                        surface: '#fff8f5',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface font-sans text-gray-800 antialiased min-h-screen flex flex-col items-center justify-center p-4 sm:p-8">
    <div class="w-full max-w-2xl bg-white rounded-3xl p-8 sm:p-14 shadow-lg border border-gray-200 text-center space-y-8 relative overflow-hidden">
        {{-- Watermark --}}
        <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center overflow-hidden z-0" aria-hidden="true">
            <span class="text-[180px] sm:text-[240px] font-black text-gray-100 tracking-tighter select-none">
                500
            </span>
        </div>

        <div class="relative z-10 space-y-4">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-50 text-amber-700 shadow-xs ring-8 ring-amber-50/50">
                <span class="material-symbols-outlined text-[42px]" aria-hidden="true">construction</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold uppercase tracking-wider">
                <span>Error 500 &bull; Temporary Interruption</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Our Sourcing Engine Encountered an Error
            </h1>

            <p class="text-sm sm:text-base text-gray-600 max-w-lg mx-auto leading-relaxed">
                Something unexpected occurred while processing your request. Our engineering desk has been automatically notified and is resolving the issue.
            </p>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <button
                type="button"
                onclick="window.location.reload()"
                class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-primary/90 transition-all cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">refresh</span>
                <span>Reload Page</span>
            </button>

            <a
                href="{{ url('/') }}"
                class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-gray-100 px-6 py-3 text-sm font-bold text-gray-800 hover:bg-gray-200 transition-all border border-gray-200"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">home</span>
                <span>Return to Home</span>
            </a>

            <a
                href="{{ \App\Support\Whatsapp::url('Hello ' . config('abatradehub.name') . ', I encountered a 500 server error while browsing your website.') }}"
                target="_blank"
                rel="noopener"
                class="w-full sm:w-auto inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white shadow-md hover:bg-secondary/90 transition-all"
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chat</span>
                <span>WhatsApp Desk</span>
            </a>
        </div>
    </div>
</body>
</html>

