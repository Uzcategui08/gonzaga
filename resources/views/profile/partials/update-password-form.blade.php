<div class="card">
    <div class="card-header bg-white border-0 py-3">
        <h3 class="card-title mb-0 d-flex align-items-center">
            {{ __('Actualizar contraseña') }}
        </h3>
    </div>
    <div class="card-body p-4">
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="update_password_current_password" class="form-label text-gray-700">{{ __('Contraseña actual') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" 
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                               id="update_password_current_password" 
                               name="current_password" 
                               autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="update_password_password" class="form-label text-gray-700">{{ __('Nueva contraseña') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-key text-muted"></i>
                        </span>
                        <input type="password" 
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                               id="update_password_password" 
                               name="password" 
                               autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="update_password_password_confirmation" class="form-label text-gray-700">{{ __('Confirmar contraseña') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-key text-muted"></i>
                        </span>
                        <input type="password" 
                               class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                               id="update_password_password_confirmation" 
                               name="password_confirmation" 
                               autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i>{{ __('Guardar') }}
                </button>
            </div>
        </form>
    </div>
</div>