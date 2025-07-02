<div class="card">
    <div class="card-header bg-white border-0 py-3">
        <h3 class="card-title mb-0 d-flex align-items-center">
            {{ __('Información') }}
        </h3>
    </div>
    <div class="card-body p-4">
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label text-gray-700">{{ __('Nombre') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-user text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required 
                               autofocus 
                               autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="username" class="form-label text-gray-700">{{ __('Usuario') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-user-tag text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control @error('username') is-invalid @enderror" 
                               id="username" 
                               name="username" 
                               value="{{ old('username', $user->username) }}" 
                               required 
                               autocomplete="username">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label text-gray-700">{{ __('Email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required 
                               autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3 p-2">
                    <p class="mb-1">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ __('Your email address is unverified.') }}
                    </p>
                    <button form="send-verification" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-envelope mr-1"></i>{{ __('Resend Verification Email') }}
                    </button>
                    
                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2 mb-0 p-2">
                            <i class="fas fa-check-circle mr-1"></i>{{ __('A new verification link has been sent to your email address.') }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="d-flex align-items-center mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i>{{ __('Guardar') }}
                </button>
            </div>
        </form>
    </div>
</div>