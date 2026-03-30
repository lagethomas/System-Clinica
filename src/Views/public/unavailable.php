<?php
$ps = $platform_settings ?? [];
$systemName = !empty($company['name']) ? $company['name'] : ($ps['system_name'] ?? 'Página do Cardápio');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporariamente Indisponível | <?php echo htmlspecialchars($systemName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        :root {
            --primary: #f59e0b;
            --bg-dark: #0a0c10;
            --text-main: #f1f5f9;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: rgba(255,255,255,0.02);
            padding: 50px 30px;
            border-radius: 32px;
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(245, 158, 11, 0.1);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        h1 { font-size: 24px; font-weight: 800; margin-bottom: 15px; }
        p { color: #94a3b8; line-height: 1.6; margin-bottom: 30px; }
        .btn-home {
            display: inline-block;
            background: var(--primary);
            color: #000;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <i data-lucide="clock-4" style="width: 40px; height: 40px;"></i>
        </div>
        <h1>Cardápio Indisponível</h1>
        <p>O cardápio desta loja encontra-se temporariamente indisponível. <br> Por favor, entre em contato com o estabelecimento ou tente novamente mais tarde.</p>
        <a href="javascript:location.reload()" class="btn-home">Tentar Novamente</a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
