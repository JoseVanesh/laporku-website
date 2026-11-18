<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h1 style="margin:0 0 8px;font-size:20px;line-height:1.3;">Verifikasi Pendaftaran Laporku</h1>
        <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#475569;">Gunakan kode berikut untuk menyelesaikan pendaftaran akun Anda:</p>
        <div style="margin:0 0 20px;padding:16px;text-align:center;border-radius:12px;background:#f1f5f9;border:1px dashed #cbd5e1;">
            <span style="font-size:32px;font-weight:700;letter-spacing:0.25em;color:#0f172a;">{{ $code }}</span>
        </div>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#334155;">Kode ini berlaku selama <strong>{{ $expiresMinutes }} menit</strong>.</p>
        <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">Jika Anda tidak merasa melakukan pendaftaran, abaikan email ini. Jangan bagikan kode ini kepada siapa pun.</p>
    </div>
</body>
</html>