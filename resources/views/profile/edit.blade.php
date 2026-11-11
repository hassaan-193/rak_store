@extends('layouts.app')

@section('title', 'Edit Profile | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Profile</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
    @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="container-fluid">

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>New Password: <small>(leave blank to keep current)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>

        </div>
    </section>
</div>

@endsection
