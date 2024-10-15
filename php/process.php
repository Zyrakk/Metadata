<?php
// Configurar el directorio de subida y tipos de archivo permitidos
$uploadDir = '../uploads/';
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Solo imágenes
$maxFileSize = 5 * 1024 * 1024; // 5MB máximo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si estamos eliminando metadatos
    if (!isset($_POST['delete'])) {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
            die("Error en la subida del archivo.");
        }

        $file = $_FILES['file'];

        // Verificar tamaño y tipo del archivo
        if ($file['size'] > $maxFileSize) die("El archivo es demasiado grande.");
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) die("Tipo de archivo no permitido.");

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            die("Extensión no permitida.");
        }

        // Generar un nombre seguro y único
        $fileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        // Mover el archivo al directorio de subida
        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            die("Error al mover el archivo.");
        }

        // Obtener metadatos usando ExifTool
        $cmd = "exiftool -json " . escapeshellarg($uploadFile);
        exec($cmd, $output, $result);
        $metadata = json_decode(implode("", $output), true);

        if ($result === 0 && !empty($metadata)) {
            $metadata = $metadata[0]; // Usamos el primer elemento del JSON
            echo "<h2>Metadatos del archivo:</h2><table>";
            foreach ($metadata as $key => $value) {
                echo "<tr><th>" . htmlspecialchars($key) . "</th><td>" . htmlspecialchars(is_array($value) ? implode(", ", $value) : $value) . "</td></tr>";
            }
            echo "</table>";

            echo "<form action='procesar.php' method='POST'>
                    <input type='hidden' name='delete' value='1'>
                    <input type='hidden' name='filePath' value='" . htmlspecialchars($uploadFile, ENT_QUOTES, 'UTF-8') . "'>
                    <button type='submit'>Eliminar Metadatos</button>
                  </form>";
        } else {
            die("Error al obtener metadatos.");
        }
    } else {
        if (!isset($_POST['filePath'])) die("Archivo no encontrado.");
        $uploadFile = $_POST['filePath'];

        // Eliminar metadatos incluyendo *Maker Notes* (opción avanzada)
        $cmd = "exiftool -all= -overwrite_original " . escapeshellarg($uploadFile);
        exec($cmd, $output, $result);

        if ($result === 0) {
            echo "<p>Metadatos eliminados correctamente. <a href='" . htmlspecialchars($uploadFile, ENT_QUOTES, 'UTF-8') . "'>Descargar archivo sin metadatos</a></p>";
        } else {
            die("Error al eliminar metadatos.");
        }
    }
}
?>
