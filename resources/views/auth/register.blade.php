<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Request Access — PAMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1c2733; --ink-dim:#5b6d7c; --ink-faint:#8fa0ac;
    --line:#e6ebef; --bg:#f6f8f9; --card:#ffffff;
    --accent:#1b7fbf; --accent-deep:#0f5e91; --accent-soft:#eaf4fb;
    --red:#c0392b; --red-soft:#fbe9e7;
  }
  *{box-sizing:border-box;}
  body{
    margin:0; font-family:'Inter',sans-serif; background:var(--bg); color:var(--ink);
    min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    -webkit-font-smoothing:antialiased;
  }
  .card{
    width:100%; max-width:440px; background:var(--card); border:1px solid var(--line);
    border-radius:16px; padding:36px 32px; box-shadow:0 10px 30px rgba(20,40,60,.06);
  }
  .brand{ display:flex; align-items:center; gap:10px; justify-content:center; margin-bottom:22px; }
  .brand .mark{
    width:36px; height:36px; border-radius:10px; background:linear-gradient(145deg,var(--accent),var(--accent-deep));
    display:flex; align-items:center; justify-content:center;
  }
  .brand .name{ font-family:'Manrope',sans-serif; font-weight:800; font-size:17px; }

  h1{ font-family:'Manrope',sans-serif; font-size:19px; font-weight:800; text-align:center; margin:0 0 4px; }
  .sub{ text-align:center; color:var(--ink-dim); font-size:13px; margin-bottom:22px; line-height:1.55; }

  .info-box{
    background:var(--accent-soft); color:var(--accent-deep); border-radius:9px; padding:11px 14px;
    font-size:12.3px; line-height:1.6; margin-bottom:22px;
  }

  .alert-error{ background:var(--red-soft); color:var(--red); padding:11px 14px; border-radius:9px; font-size:12.8px; margin-bottom:18px; line-height:1.5; }

  label{ display:block; font-size:12.6px; font-weight:650; color:var(--ink-dim); margin-bottom:6px; }
  .field{ margin-bottom:16px; }
  .two-col{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  input[type=text], input[type=email], input[type=password], select{
    width:100%; padding:11px 13px; border:1.5px solid var(--line); border-radius:9px;
    font-size:14px; font-family:'Inter',sans-serif; color:var(--ink); background:#fff; transition:border-color .15s;
  }
  input:focus, select:focus{ outline:none; border-color:var(--accent); }
  .field-error{ color:var(--red); font-size:12px; margin-top:5px; }
  .hint{ font-size:11.5px; color:var(--ink-faint); margin-top:5px; }

  .btn-primary{
    width:100%; background:var(--accent); color:#fff; border:none; border-radius:9px;
    padding:12px; font-size:14.5px; font-weight:650; cursor:pointer; transition:background .15s; margin-top:6px;
  }
  .btn-primary:hover{ background:var(--accent-deep); }

  .footer-link{ text-align:center; margin-top:20px; font-size:13px; color:var(--ink-dim); }
  .footer-link a{ color:var(--accent); font-weight:600; text-decoration:none; }
  .footer-link a:hover{ text-decoration:underline; }
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

  <h1>Request access</h1>
  <div class="sub">Fill this out to request a PAMS account. Access is not granted automatically.</div>

  <div class="info-box">
    ⓘ Your request will be reviewed by an Admin. The role below is a request only —
    the Admin assigns your actual role and activates your account before you can log in.
  </div>

  @if ($errors->any())
    <div class="alert-error">
      @foreach ($errors->all() as $error)
        {{ $error }}@if(!$loop->last)<br>@endif
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="field">
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
      @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username">
      @error('email') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
      <label for="requested_role">Requested role</label>
      <select id="requested_role" name="requested_role" required>
        <option value="" disabled {{ old('requested_role') ? '' : 'selected' }}>Select a role…</option>
        <option value="hr" {{ old('requested_role')==='hr' ? 'selected' : '' }}>HR Officer</option>
        <option value="records" {{ old('requested_role')==='records' ? 'selected' : '' }}>Records Officer</option>
        <option value="manager" {{ old('requested_role')==='manager' ? 'selected' : '' }}>Manager</option>
        <option value="admin" {{ old('requested_role')==='admin' ? 'selected' : '' }}>Administrator</option>
      </select>
      <div class="hint">This is what you're applying for — final role is set by Admin.</div>
      @error('requested_role') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="two-col">
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password">
        @error('password') <div class="field-error">{{ $message }}</div> @enderror
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
      </div>
    </div>

    <button type="submit" class="btn-primary">Submit request</button>
  </form>

  <div class="footer-link">
    Already have an account? <a href="{{ route('login') }}">Log in</a>
  </div>
</div>

</body>
</html>