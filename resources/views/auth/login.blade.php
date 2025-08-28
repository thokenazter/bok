<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- 3D Login Styles -->
    <link href="{{ asset('css/3d-login.css') }}" rel="stylesheet">
    <link href="{{ asset('css/3d-login-dark.css') }}" rel="stylesheet">
    <link href="{{ asset('css/crab-extras.css') }}" rel="stylesheet">
    <link href="{{ asset('css/page-transitions.css') }}" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-container">
        <!-- Floating Background Shapes -->
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <!-- Main Login Card -->
        <div class="login-card">
            <!-- 3D Character -->
            <div class="character-container">
                <div class="character" role="img" aria-label="Login mascot character">
                    <div class="character-head">
                        <div class="character-eyes">
                            <div class="eye"></div>
                            <div class="eye"></div>
                        </div>
                        <div class="character-mouth"></div>
                        <div class="character-hands">
                            <div class="hand"></div>
                            <div class="hand"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Title -->
            {{-- <h1 class="login-title text-center text-lg">{{ __('Welcome Back') }}</h1> --}}
            
            <!-- Success Message -->
            @session('status')
                <div class="success-message" role="alert">
                    {{ $value }}
                </div>
            @endsession
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="error-messages" role="alert">
                    @foreach ($errors->all() as $error)
                        <div class="error-text">{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            
            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Alamat Email') }}</label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="form-input @error('email') error @enderror" 
                        placeholder="Masukan Alamat Email"
                        required 
                        autofocus 
                        autocomplete="username"
                        aria-describedby="email-error"
                    >
                    @error('email')
                        <span class="error-text" id="email-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="form-input @error('password') error @enderror" 
                        placeholder="Masukan Password"
                        required 
                        autocomplete="current-password"
                        aria-describedby="password-error"
                    >
                    @error('password')
                        <span class="error-text" id="password-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Form Options -->
                <div class="form-options">
                    <label class="Ingat Saya">
                        <input 
                            type="checkbox" 
                            id="remember_me" 
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>{{ __('Ingat Saya') }}</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <div class="forgot-password">
                            <a href="{{ route('password.request') }}">
                                {{ __('Lupa Password?') }}
                            </a>
                        </div>
                    @endif
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="login-button" aria-describedby="login-help">
                    {{ __('Masuk') }}
                </button>
                
                <!-- Register Link -->
                @if (Route::has('register'))
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="{{ route('register') }}" data-page-transition style="color: white; opacity: 0.8; text-decoration: none; font-size: 14px;">
                            {{ __("Belum Punya Akun? Daftar Disini") }}
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
    
    <!-- 3D Login JavaScript -->
    <script src="{{ asset('js/3d-login.js') }}"></script>
    <script src="{{ asset('js/page-transitions-simple.js') }}"></script>
    
    <!-- Laravel Error/Success State -->
    <script>
        // Pass Laravel state to JavaScript
        @if ($errors->any())
            window.hasValidationErrors = true;
        @endif
        
        @session('status')
            window.hasSuccessMessage = true;
        @endsession
        
        // Override form submission to handle success animation properly
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // Let the enhanced script handle the submission
                    // This ensures we can show celebration before redirect
                });
            }
        });
        
        // Additional character state announcements for screen readers
        document.addEventListener('DOMContentLoaded', function() {
            const character = document.querySelector('.character');
            const liveRegion = document.getElementById('character-status');
            
            if (character && liveRegion) {
                // Announce character state changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const classList = character.classList;
                            if (classList.contains('happy')) {
                                liveRegion.textContent = 'Character is happy';
                            } else if (classList.contains('covering-eyes')) {
                                liveRegion.textContent = 'Character is covering eyes for privacy';
                            } else if (classList.contains('sad')) {
                                liveRegion.textContent = 'Character is sad due to error';
                            }
                        }
                    });
                });
                
                observer.observe(character, { attributes: true });
            }
        });
    </script>
    
    <!-- Screen Reader Only Content -->
    <div class="sr-only" style="position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;">
        <div id="character-status" aria-live="polite" aria-atomic="true"></div>
        <div id="login-help">Use Tab to navigate between form fields. The character will react to your interactions.</div>
    </div>
    
    @livewireScripts
</body>
</html>