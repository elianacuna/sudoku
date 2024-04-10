<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku</title>
    <link rel="stylesheet" href="./css/sudoku.css">
</head>
<body>
    
    <p>Haber si me puedes ganar:
    <?php
    // Verificar si se ha pasado el nombre como parámetro en la URL
    if (isset($_GET["nombre"])) {
        $nombre = $_GET["nombre"];
        // Imprimir el nombre del jugador si está disponible
        if (!empty($nombre)) {
            echo htmlspecialchars($nombre); // htmlspecialchars para evitar problemas de seguridad
        }
    }
    ?>
    </p>
    
</body>
</html>
