<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - DoaKu Kids</title>
    
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Pastikan npm run dev dijalankan agar CSS bekerja optimal, namun ini file standar bawaan jika Vite mati -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gradient-to-br from-yellow-100 via-amber-200 to-yellow-300 text-[#1b1b18] flex items-center justify-center min-h-screen font-sans relative overflow-hidden">
    
    <!-- Floating bubbles background (Sama dengan tema Welcome) -->
    <div class="fixed inset-0 z-[-1] pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full mix-blend-overlay filter blur-xl opacity-60"></div>
        <div class="absolute bottom-20 right-10 w-48 h-48 bg-white rounded-full mix-blend-overlay filter blur-xl opacity-60"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-white rounded-full mix-blend-overlay filter blur-xl opacity-50"></div>
    </div>

    <div class="w-full max-w-md p-6">
        <div class="bg-white/90 backdrop-blur-xl rounded-[2.5rem] shadow-[0_15px_40px_rgba(0,0,0,0.12)] border-4 border-white p-8 md:p-10 relative overflow-hidden">
            <!-- Decorative corner -->
            <div class="absolute top-0 right-0 w-24 h-24 bg-pink-100 rounded-bl-[80px] -z-0"></div>
            
            <div class="relative z-10 text-center mb-8">
                <a href="{{ url('/') }}" class="inline-block mb-4 text-6xl hover:scale-110 transition-transform drop-shadow-md">🌟</a>
                <h1 class="text-3xl font-black text-gray-800 drop-shadow-sm">Selamat Datang!</h1>
                <p class="text-gray-500 font-bold mt-2">Yuk login untuk mulai belajar doa</p>
            </div>

            <!-- Form Autentikasi -->
            <form method="POST" action="{{ route('login') }}" class="relative z-10 space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-black text-gray-700 mb-2">Email Kamu</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full border-4 border-gray-100 rounded-2xl px-5 py-3 focus:ring-0 focus:border-pink-400 outline-none transition-all text-lg font-bold text-gray-700 bg-gray-50 shadow-inner"
                        placeholder="contoh@email.com">
                    <!-- Pesan Error Laravel -->
                    @error('email')
                        <p class="text-rose-500 text-sm font-bold mt-2 bg-rose-50 p-2 rounded-lg border border-rose-100">⚠ {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-black text-gray-700 mb-2">Password Rahasia</label>
                    <input id="password" type="password" name="password" required
                        class="w-full border-4 border-gray-100 rounded-2xl px-5 py-3 focus:ring-0 focus:border-blue-400 outline-none transition-all text-lg font-bold text-gray-700 bg-gray-50 shadow-inner"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-rose-500 text-sm font-bold mt-2 bg-rose-50 p-2 rounded-lg border border-rose-100">⚠ {{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-black px-6 py-4 rounded-2xl shadow-[0_6px_0_#be185d] hover:shadow-[0_3px_0_#be185d] hover:translate-y-1 transition-all text-xl">
                        Masuk Sekarang! 🚀
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center relative z-10 border-t-2 border-dashed border-gray-200 pt-6">
                <p class="text-gray-500 font-bold">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-600 hover:underline decoration-4 underline-offset-4 transition-colors">
                        Daftar di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
