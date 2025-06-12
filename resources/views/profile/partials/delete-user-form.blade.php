<div class="card">
    <div class="card-header bg-white border-0 py-3">
        <h3 class="card-title mb-0 d-flex align-items-center">
            <i class="fas fa-trash-alt text-danger mr-2"></i>
            {{ __('Delete Account') }}
        </h3>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-danger">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-danger mr-2" style="font-size: 1.5rem"></i>
                <div>
                    <p class="mb-0">
                        {{ __('Una vez que elimines tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.') }}
                    </p>
                </div>
            </div>
        </div>

        <button type="button" 
                class="btn btn-danger mt-3" 
                data-toggle="modal" 
                data-target="#confirm-user-deletion">
            <i class="fas fa-trash-alt mr-1"></i>{{ __('Delete Account') }}
        </button>

        <div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionLabel">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                            {{ __('¿Estás seguro de que deseas eliminar tu cuenta?') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle text-danger mr-2" style="font-size: 1.5rem"></i>
                            <div>
                                <p class="mb-0 text-dark">
                                    {{ __('Una vez que elimines tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor ingresa tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.') }}
                                </p>
                            </div>
                        </div>
                        <form method="post" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')
                            <div class="form-group mt-4">
                                <label for="password" class="form-label text-dark">{{ __('Contraseña') }}</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="{{ __('Contraseña') }}">
                                @error('password')
                                    <span class="invalid-feedback text-dark" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    {{ __('Cancelar') }}
                                </button>
                                <button type="submit" class="btn btn-danger ms-3">
                                    {{ __('Eliminar cuenta') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>