<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Upesi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            background: #f8fafc;
            color: #1e293b;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #166534;
            border-left: 5px solid #22c55e;
            padding-left: 20px;
            margin-bottom: 16px;
        }

        .meta {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 32px;
            padding-left: 25px;
        }

        .content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .content h1, .content h2, .content h3, .content h4 {
            color: #166534;
            margin-top: 24px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .content h1 { font-size: 1.8rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
        .content h2 { font-size: 1.5rem; }
        .content h3 { font-size: 1.3rem; }
        .content p {
            margin-bottom: 16px;
            color: #334155;
        }
        .content ul, .content ol {
            margin: 16px 0 16px 24px;
            color: #334155;
        }
        .content li {
            margin: 8px 0;
        }
        .content strong {
            color: #166534;
        }
        .content a {
            color: #16a34a;
            text-decoration: none;
            border-bottom: 1px solid #bbf7d0;
        }
        .content a:hover {
            color: #15803d;
            border-bottom-color: #15803d;
        }
        .content hr {
            margin: 24px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
        .content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 16px 0;
        }
        .content blockquote {
            border-left: 4px solid #22c55e;
            padding-left: 20px;
            margin: 16px 0;
            color: #475569;
            font-style: italic;
        }
        .content pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 12px;
            overflow-x: auto;
            margin: 16px 0;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 24px;
            font-size: 0.8rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        a {
            color: #16a34a;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .container {
                padding: 20px 16px;
            }
            .content {
                padding: 24px 20px;
            }
            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 {{ $title }}</h1>
        <div class="meta">
            📅 Dernière mise à jour : {{ \Carbon\Carbon::parse($updated_at)->format('d/m/Y') }}
        </div>

        <div class="content">
            {!! $htmlContent !!}
        </div>

        <footer>
            © {{ date('Y') }} Upesi - Marketplace agricole<br>
            <a href="https://u-pesi.com">https://u-pesi.com</a>
        </footer>
    </div>
</body>
</html>
