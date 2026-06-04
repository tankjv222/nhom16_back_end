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
    <!-- QR Check-in removed: using image-based check-in only -->

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Image Check-in</h5>
        <form id="image-checkin-form" method="post" action="{{ route('student.checkin.image') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Upload Photo</label>
            <input type="file" name="image" id="image-file-input" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Or capture from webcam</label>
            <div class="mb-2">
              <video id="camera-video" autoplay playsinline style="width:100%;max-width:480px;display:none;border:1px solid #ddd;background:#000"></video>
              <div id="camera-message" class="small text-muted">Nhấn 'Open Camera' để bật webcam.</div>
            </div>
            <div class="btn-group mb-2">
              <button type="button" id="open-camera-btn" class="btn btn-outline-primary">Open Camera</button>
              <button type="button" id="take-photo-btn" class="btn btn-primary" disabled>Take Photo</button>
              <button type="button" id="stop-camera-btn" class="btn btn-secondary" disabled>Stop Camera</button>
            </div>
            <div class="mt-2">
              <img id="photo-preview" style="max-width:320px;display:none;border:1px solid #ccc;" alt="preview">
            </div>
          </div>
          <button type="submit" id="upload-btn" class="btn btn-primary">Upload & Check</button>
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
<script>
  // Webcam capture for image check-in
  let _cameraStream = null;
  const imageForm = document.getElementById('image-checkin-form');
  const videoEl = document.getElementById('camera-video');
  const openBtn = document.getElementById('open-camera-btn');
  const takeBtn = document.getElementById('take-photo-btn');
  const stopBtn = document.getElementById('stop-camera-btn');
  const previewImg = document.getElementById('photo-preview');

  function enableCameraControls(enabled) {
    if (openBtn) openBtn.disabled = enabled;
    if (takeBtn) takeBtn.disabled = !enabled;
    if (stopBtn) stopBtn.disabled = !enabled;
  }

  async function openCamera() {
    try {
      _cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
      videoEl.srcObject = _cameraStream;
      videoEl.style.display = 'block';
      document.getElementById('camera-message').innerText = 'Camera is on.';
      enableCameraControls(true);
    } catch (err) {
      console.error('openCamera error', err);
      document.getElementById('camera-message').innerText = 'Không thể mở camera: ' + (err.message || err);
    }
  }

  function stopCameraForImage() {
    if (_cameraStream) {
      _cameraStream.getTracks().forEach(t => t.stop());
      _cameraStream = null;
    }
    if (videoEl) videoEl.style.display = 'none';
    enableCameraControls(false);
    document.getElementById('camera-message').innerText = 'Camera stopped.';
  }

  function takePhotoAndPreview() {
    if (!videoEl || !videoEl.srcObject) return;
    const canvas = document.createElement('canvas');
    canvas.width = videoEl.videoWidth || 640;
    canvas.height = videoEl.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(blob => {
      // preview
      const url = URL.createObjectURL(blob);
      previewImg.src = url;
      previewImg.style.display = 'block';
      // set file input (optional) by creating a File and using DataTransfer
      const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
      const dt = new DataTransfer();
      dt.items.add(file);
      const fileInput = document.getElementById('image-file-input');
      if (fileInput) fileInput.files = dt.files;
    }, 'image/jpeg', 0.9);
  }

  // submit handler: if user uses file input or preview, submit via fetch to the web route
  if (imageForm) {
    imageForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const fileInput = document.getElementById('image-file-input');
      const file = fileInput && fileInput.files && fileInput.files[0];
      if (!file) return alert('Vui lòng chọn file hoặc chụp ảnh trước khi upload.');
      const fd = new FormData();
      fd.append('image', file);
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      try {
        const res = await fetch("{{ route('student.checkin.image') }}", {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });
        let j = null;
        try { j = await res.json().catch(()=>null); } catch(e) { j = null; }
        if (!res.ok) {
          const msg = j?.message || j?.error || res.statusText || 'Unknown error';
          return alert('Upload failed: ' + msg);
        }
        const confidence = j?.rekognition?.confidence ?? j?.confidence ?? 'N/A';
        alert('Ảnh đã được gửi. Kết quả: ' + confidence);
      } catch (err) {
        console.error('upload error', err);
        alert('Lỗi khi gửi ảnh');
      }
    });
  }

  if (openBtn) openBtn.addEventListener('click', openCamera);
  if (stopBtn) stopBtn.addEventListener('click', stopCameraForImage);
  if (takeBtn) takeBtn.addEventListener('click', takePhotoAndPreview);
</script>
@endsection
