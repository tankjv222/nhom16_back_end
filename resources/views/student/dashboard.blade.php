@extends('layouts.app')

@section('title','Student Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Xin chào, {{ $student->first_name }} {{ $student->last_name }}</h3>
  <form method="post" action="{{ route('student.logout') }}">
    @csrf
    <button class="btn btn-outline-secondary">Logout</button>
  </form>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">QR Check-in</h5>
        <form id="qr-form" method="post" action="{{ route('student.checkin.qr') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Event ID</label>
            <div class="input-group">
              <input id="event_id_input" type="text" name="event_id" class="form-control" required>
              <button id="scan-btn" type="button" class="btn btn-outline-primary" onclick="startScanner()">Scan QR</button>
            </div>
          </div>
          <button class="btn btn-success">Check-in with QR</button>
        </form>

        <div id="qr-reader" class="mt-3" style="display:none;"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Image Check-in</h5>
        <form method="post" action="{{ route('student.checkin.image') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Upload Photo</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
          </div>
          <button class="btn btn-primary">Upload & Check</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Recent Attendances</h5>
        <ul class="list-group">
          @foreach($attendances as $a)
            <li class="list-group-item">
              <strong>{{ $a->method }}</strong> @if($a->event_id) - {{ $a->event_id }} @endif
              <div class="small text-muted">{{ $a->created_at }}</div>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  let html5Scanner = null;
  function startScanner(){
    const reader = document.getElementById('qr-reader');
    reader.style.display = 'block';
    if (html5Scanner){
      return;
    }
    html5Scanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: 250 });
    html5Scanner.render(function(decodedText, decodedResult){
      // Fill the event id and submit the form
      document.getElementById('event_id_input').value = decodedText;
      document.getElementById('qr-form').submit();
      try{ html5Scanner.clear(); }catch(e){}
    }, function(error){
      // ignore scan errors
    });
  }
</script>
@endsection
