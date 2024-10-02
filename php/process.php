<?php
// Configuración y validaciones iniciales
$uploadDir = '../uploads/';
$cleanedDir = '../cleaned_files/';
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Manejo de subida de archivo y obtención de metadatos
    if (!isset($_POST['delete'])) {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
            die("Error en la subida del archivo.");
        }

        $file = $_FILES['file'];

        if ($file['size'] > $maxFileSize) {
            die("El archivo excede el tamaño máximo permitido de 5MB.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            die("El tipo de archivo no está permitido. Solo se permiten archivos JPEG, PNG y GIF.");
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("La extensión del archivo no está permitida.");
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            die("Error al mover el archivo subido.");
        }

        // Procesar y mostrar metadatos
        $cmd = "exiftool -json " . escapeshellarg($uploadFile);
        exec($cmd, $output, $result);
        $metadata = json_decode(implode("", $output), true);

        if ($result === 0 && !empty($metadata)) {
            $metadata = $metadata[0];
            echo "<div class='metadata'>";
            echo "<h2>Metadatos del archivo:</h2>";
            echo "<table>";
            foreach ($metadata as $key => $value) {
                echo "<tr><th>" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "</th><td>" . htmlspecialchars(is_array($value) ? implode(", ", $value) : $value, ENT_QUOTES, 'UTF-8') . "</td></tr>";
            }
            echo "</table>";
            echo "</div>";

            echo "<form action='process.php' method='POST'>";
            echo "<input type='hidden' name='delete' value='1'>";
            echo "<input type='hidden' name='filePath' value='" . htmlspecialchars($uploadFile, ENT_QUOTES, 'UTF-8') . "'>";
            echo "<button type='submit'>Eliminar Metadatos</button>";
            echo "</form>";
        } else {
            die("Error al obtener metadatos.");
        }
    } else {
        // Eliminación de metadatos
        if (!isset($_POST['filePath'])) {
            die("Archivo no encontrado.");
        }

        $uploadFile = $_POST['filePath'];
        $fileExtension = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $fileName = basename($uploadFile);
        $cleanedFile = $cleanedDir . $fileName;

        $cmd = "exiftool -all= -o " . escapeshellarg($cleanedFile) . " " . escapeshellarg($uploadFile);
        exec($cmd, $output, $result);

        if ($result === 0) {
            unlink($uploadFile);

            echo "<div class='success'>
                    <p>Metadatos eliminados correctamente. <a href='" . htmlspecialchars($cleanedFile, ENT_QUOTES, 'UTF-8') . "'>Descargar archivo sin metadatos</a></p>
                </div>";
        } else {
            die("Error al eliminar metadatos.");
        }
    }
}
?>
