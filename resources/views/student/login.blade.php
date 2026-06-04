@extends('layouts.app')

@section('title','Student Login')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Student Login</h5>
        <form method="post" action="{{ route('student.login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Student ID or Email</label>
            <input type="text" name="student_id" value="{{ old('student_id') }}" class="form-control">
            @error('student_id')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control">
            @error('password')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
          <button class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
