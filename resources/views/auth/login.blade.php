{{-- Halaman login. Berdiri sendiri, tidak memakai layout induk. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | SMK Bina Bangsa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 font-sans text-slate-800 antialiased">

    <div class="w-full max-w-sm">

        {{-- Merek sekolah. --}}
        <div class="mb-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-teal-700 text-lg font-bold text-white">BB</span>
            <h1 class="mt-3 text-xl font-bold text-slate-900">BinaBangsa Pay</h1>
            <p class="mt-1 text-sm text-slate-500">Sistem Pembayaran SPP SMK Bina Bangsa</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            {{-- Pesan gagal dari validasi login. --}}
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.proses') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" id="username"
                           value="{{ old('username') }}"
                           required autofocus autocomplete="username"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" id="password"
                           required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-300">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            SMK Bina Bangsa, Sistem Pembayaran SPP
        </p>
    </div>
</body>
</html>
