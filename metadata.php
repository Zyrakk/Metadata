<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Eliminador de Metadatos</title>
    <link rel="stylesheet" href="sources/styles_metadata.css">
    <script defer src="sources/detect_darkmode.js"></script>
</head>
<body>
    <div class="container">
        <h1>Eliminador de Metadatos</h1>
        <br><br>
        <form action="php/process.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <label for="file" class="upload-label">Seleccionar Imagen</label>
            <input type="file" name="file" id="file" required>
            <button type="submit" id="submitBtn">Mostrar Metadatos</button>
        </form>

        <div id="metadataSection" style="display:none;">
            <h2>Metadatos del Archivo:</h2>
            <pre id="metadataDisplay"></pre>
            <form action="php/process.php" method="POST" id="deleteMetadataForm">
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
