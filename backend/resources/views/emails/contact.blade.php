<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: system-ui, sans-serif; color: #222; background: #f5f5f5; margin: 0; padding: 2rem; }
    .card { background: white; border-radius: 8px; padding: 2rem; max-width: 560px; margin: 0 auto; }
    .label { font-size: .8rem; color: #888; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .25rem; }
    .value { font-size: 1rem; margin-bottom: 1.25rem; }
    .message { background: #f9f9f9; border-left: 3px solid #1a1a2e; padding: 1rem; border-radius: 0 4px 4px 0; white-space: pre-wrap; }
    .footer { font-size: .8rem; color: #aaa; margin-top: 1.5rem; }
  </style>
</head>
<body>
  <div class="card">
    <h2 style="color:#1a1a2e;margin-top:0">Nowa wiadomość ze strony szachowej ♟</h2>

    <div class="label">Imię i nazwisko</div>
    <div class="value">{{ $fromName }}</div>

    @if($replyTo)
    <div class="label">Email (do odpowiedzi)</div>
    <div class="value"><a href="mailto:{{ $replyTo }}">{{ $replyTo }}</a></div>
    @endif

    <div class="label">Wiadomość</div>
    <div class="message">{{ $body }}</div>

    <div class="footer">
      Wiadomość wysłana przez formularz kontaktowy na stronie szachowej.
    </div>
  </div>
</body>
</html>
