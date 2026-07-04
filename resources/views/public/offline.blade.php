<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>CheckPraia - Sem Conexão</title>
    <meta name="color-scheme" content="dark light">
    <meta name="theme-color" content="#020617" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#dee4ec" media="(prefers-color-scheme: light)">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: dark light; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #070a13;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            padding: 24px;
            text-align: center;
            transition: background 0.3s ease, color 0.3s ease;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(120% 120% at 50% 10%, #0d1527 0%, #070a13 100%);
            z-index: -1;
        }
        .card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 32px;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .icon { font-size: 64px; margin-bottom: 16px; display: block; }
        h1 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        p { font-size: 14px; line-height: 1.6; color: #94a3b8; margin-bottom: 24px; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 14px 28px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn:active { transform: scale(0.97); }
        @media (prefers-color-scheme: light) {
            body { background: #dee4ec; color: #0b1221; }
            body::before {
                background: radial-gradient(120% 120% at 50% 10%, #d4dae3 0%, #dee4ec 100%);
            }
            .card { background: rgba(255, 255, 255, 0.8); border-color: rgba(0, 0, 0, 0.18); box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.10); }
            p { color: #475569; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        .icon { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">🌊</span>
        <h1>Sem Conexão</h1>
        <p>Não foi possível carregar esta página de momento. Verifica a tua ligação à internet e tenta novamente.</p>
        <button class="btn" onclick="location.reload()">Tentar Novamente</button>
    </div>
</body>
</html>
