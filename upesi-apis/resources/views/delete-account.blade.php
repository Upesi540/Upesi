<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer mon compte - Upesi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 40px;
            text-align: center;
        }

        .icon {
            font-size: 64px;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .app-name {
            color: #16a34a;
            display: inline-block;
            font-weight: 800;
        }

        .warning-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            border-radius: 16px;
            text-align: left;
            margin: 24px 0;
        }

        .warning-box p {
            color: #7f1d1d;
            margin: 8px 0;
            font-size: 14px;
        }

        .warning-box strong {
            color: #dc2626;
        }

        .btn-mail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 60px;
            transition: all 0.2s ease;
            margin: 16px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-mail:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(22, 163, 74, 0.4);
        }

        .info-text {
            color: #475569;
            font-size: 14px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .info-text a {
            color: #16a34a;
            text-decoration: none;
        }

        .info-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .card {
                padding: 28px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .btn-mail {
                font-size: 16px;
                padding: 14px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            🧑‍🌾
        </div>

        <h1>
            Supprimer mon compte <span class="app-name">Upesi</span>
        </h1>

        <p style="color: #475569; margin-bottom: 24px;">
            Cette action est irréversible. Toutes vos données seront effacées définitivement.
        </p>

        <div class="warning-box">
            <p>⚠️ <strong>Attention :</strong> Cette action supprimera définitivement :</p>
            <p>• Votre profil et toutes vos informations personnelles</p>
            <p>• L'historique de vos transactions</p>
            <p>• Toutes vos annonces (en tant que vendeur)</p>
            <p>• L'ensemble de vos données associées au compte</p>
        </div>

        <!-- Bouton mailto -->
        <a href="mailto:support@upesi.com?subject=SUPPRESSION%20COMPTE%20UPESI&body=Bonjour,%0A%0AJe souhaite supprimer définitivement mon compte Upesi associé à cette adresse email.%0A%0AMerci de procéder à la suppression.%0A%0ACordialement"
           class="btn-mail">
            📧 Envoyer une demande de suppression
        </a>

        <div class="info-text">
            <p>📌 <strong>Comment ça marche ?</strong></p>
            <p>1. Cliquez sur le bouton ci-dessus</p>
            <p>2. Votre logiciel de messagerie s'ouvrira automatiquement</p>
            <p>3. Envoyez l'email <strong>depuis l'adresse associée à votre compte Upesi</strong></p>
            <p>4. Nous traiterons votre demande sous 72h</p>
            <br>
            <p>✉️ Vous pouvez aussi nous écrire directement à : <a href="mailto:support@u-pesi.com">support@u-pesi.com</a></p>
        </div>
    </div>
</body>
</html>
