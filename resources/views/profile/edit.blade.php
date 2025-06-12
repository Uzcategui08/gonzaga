@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0">{{ __('Configuración de perfil') }}</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-12">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
