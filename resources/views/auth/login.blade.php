<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Log in — PAMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1c2733; --ink-dim:#5b6d7c; --ink-faint:#8fa0ac;
    --line:#e6ebef; --bg:#f6f8f9; --card:#ffffff;
    --accent:#1b7fbf; --accent-deep:#0f5e91; --accent-soft:#eaf4fb;
    --red:#c0392b; --red-soft:#fbe9e7;
    --green:#1b8a4a; --green-soft:#e6f6ec;
  }
  *{box-sizing:border-box;}
  body{
    margin:0; font-family:'Inter',sans-serif; background:var(--bg); color:var(--ink);
    min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    -webkit-font-smoothing:antialiased;
  }
  .card{
    width:100%; max-width:400px; background:var(--card); border:1px solid var(--line);
    border-radius:16px; padding:36px 32px; box-shadow:0 10px 30px rgba(20,40,60,.06);
  }
  .brand{ display:flex; align-items:center; gap:10px; justify-content:center; margin-bottom:22px; }
  .brand .mark{
    width:36px; height:36px; border-radius:10px; background:linear-gradient(145deg,var(--accent),var(--accent-deep));
    display:flex; align-items:center; justify-content:center;
  }
  .brand .name{ font-family:'Manrope',sans-serif; font-weight:800; font-size:17px; }

  h1{ font-family:'Manrope',sans-serif; font-size:19px; font-weight:800; text-align:center; margin:0 0 4px; }
  .sub{ text-align:center; color:var(--ink-dim); font-size:13px; margin-bottom:26px; }

  .alert{ padding:11px 14px; border-radius:9px; font-size:12.8px; margin-bottom:18px; line-height:1.5; }
  .alert-success{ background:var(--green-soft); color:var(--green); }
  .alert-error{ background:var(--red-soft); color:var(--red); }

  label{ display:block; font-size:12.6px; font-weight:650; color:var(--ink-dim); margin-bottom:6px; }
  .field{ margin-bottom:16px; }
  input[type=email], input[type=password]{
    width:100%; padding:11px 13px; border:1.5px solid var(--line); border-radius:9px;
    font-size:14px; font-family:'Inter',sans-serif; color:var(--ink); transition:border-color .15s;
  }
  input:focus{ outline:none; border-color:var(--accent); }
  .field-error{ color:var(--red); font-size:12px; margin-top:5px; }

  .row-between{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; font-size:12.6px; }
  .checkbox-label{ display:flex; align-items:center; gap:7px; color:var(--ink-dim); cursor:pointer; }

  .btn-primary{
    width:100%; background:var(--accent); color:#fff; border:none; border-radius:9px;
    padding:12px; font-size:14.5px; font-weight:650; cursor:pointer; transition:background .15s;
  }
  .btn-primary:hover{ background:var(--accent-deep); }

  .footer-link{ text-align:center; margin-top:22px; font-size:13px; color:var(--ink-dim); }
  .footer-link a{ color:var(--accent); font-weight:600; text-decoration:none; }
  .footer-link a:hover{ text-decoration:underline; }

  .note{ text-align:center; font-size:11.5px; color:var(--ink-faint); margin-top:18px; line-height:1.6; }
</style>
</head>
<body>

<div class="card">
  <div class="brand">
    <div class="mark">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20c0-3 3-5 6-5s6 2 6 5"/><path d="M14 20c0-2.2 1.8-4 4-4"/></svg>
    </div>
    <div class="name">PAMS</div>
  </div>

  <h1>Log in to your account</h1>
  <div class="sub">Personnel Appointment Management System</div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-error">
      @foreach ($errors->all() as $error)
        {{ $error }}@if(!$loop->last)<br>@endif
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="field">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
      @error('email') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
      @error('password') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="row-between">
      <label class="checkbox-label">
        <input type="checkbox" name="remember"> Remember me
      </label>
    </div>

    <button type="submit" class="btn-primary">Log in</button>
  </form>

  <div class="footer-link">
    Don't have an account? <a href="{{ route('register') }}">Request access</a>
  </div>

  <div class="note">
    Role is assigned by your Admin and applied automatically after login —
    there is no role selector here.
  </div>
</div>

</body>
</html>