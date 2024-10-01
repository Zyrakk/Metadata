<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Eliminador de Metadatos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Eliminador de Metadatos de Imágenes</h1>
        <form action="procesar.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <!-- Asignamos el 'for' con el mismo ID que el input -->
            <label for="file" class="upload-label">Sube una imagen (JPEG, PNG o GIF, máximo 5MB):</label>
            <!-- Input file oculto -->
            <input type="file" name="file" id="file" required>
            <button type="submit" id="submitBtn">Mostrar Metadatos</button>
        </form>

        <div id="metadataSection" style="display:none;">
            <h2>Metadatos del Archivo:</h2>
            <pre id="metadataDisplay"></pre>
            <form action="process.php" method="POST" id="deleteMetadataForm">
                <input type="hidden" name="delete" value="1">
                <input type="hidden" name="filePath" id="filePath" value="">
                <button type="submit">Eliminar Metadatos</button>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['metadata'])): ?>
        <script>
            document.getElementById('metadataSection').style.display = 'block';
            document.getElementById('metadataDisplay').textContent = <?= json_encode($_GET['metadata']); ?>;
            document.getElementById('filePath').value = <?= json_encode($_GET['file']); ?>;
        </script>
    <?php endif; ?>
</body>
</html>
