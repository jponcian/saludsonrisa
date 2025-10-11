<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carnet SomosSalud</title>
    <meta name="viewport" content="width=1200">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .carnet-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            margin-top: 40px;
        }

        .carnet-img {
            width: 1000px;
            height: 650px;
            box-shadow: 0 2px 12px #0002;
            border-radius: 16px;
            background: #fff;
            object-fit: cover;
        }

        @media (max-width: 2200px) {
            .carnet-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <div class="carnet-container">
        <img class="carnet-img" src="carnet.php?id=<?php echo urlencode($_GET['id'] ?? ''); ?>" alt="Carnet Anverso">
        <img class="carnet-img" src="carnet_reverso.php?id=<?php echo urlencode($_GET['id'] ?? ''); ?>"
            alt="Carnet Reverso">
    </div>
</body>

</html>