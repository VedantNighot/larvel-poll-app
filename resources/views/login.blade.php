@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-header">Login</div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login as User/Admin (Demo)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
