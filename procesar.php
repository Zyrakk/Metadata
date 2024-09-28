<?php
// Configurar el directorio de subida y tipos de archivo permitidos
$uploadDir = 'uploads/';
$cleanedDir = 'cleaned_files/';
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Solo permitir tipos de imagen
$maxFileSize = 5 * 1024 * 1024; // Tamaño máximo del archivo: 5MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si se está eliminando o mostrando metadatos
    if (!isset($_POST['delete'])) {
        // Verificar si se ha subido un archivo
        if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
            die("Error en la subida del archivo.");
        }

        $file = $_FILES['file'];

        // Verificar el tamaño del archivo
        if ($file['size'] > $maxFileSize) {
            die("El archivo excede el tamaño máximo permitido de 5MB.");
        }

        // Verificar el tipo MIME del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            die("El tipo de archivo no está permitido. Solo se permiten archivos JPEG, PNG y GIF.");
        }

        // Evitar la ejecución de scripts maliciosos verificando la extensión
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Extensiones permitidas
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("La extensión del archivo no está permitida.");
        }

        // Generar un nombre de archivo seguro y único
        $fileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        // Subir el archivo al directorio de destino
        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            die("Error al mover el archivo subido.");
        }

        // Obtener los metadatos usando ExifTool
        $cmd = "exiftool -json " . escapeshellarg($uploadFile); // Usamos formato JSON para mayor facilidad
        exec($cmd, $output, $result);

        // Convertir la salida de ExifTool a un array asociativo
        $metadata = json_decode(implode("", $output), true);

        // Mostrar los metadatos de manera organizada
        if ($result === 0 && !empty($metadata)) {
            $metadata = $metadata[0]; // ExifTool puede devolver varios resultados, seleccionamos el primero
            echo "<div class='metadata'>";
            echo "<h2>Metadatos del archivo:</h2>";
            echo "<table>";
            foreach ($metadata as $key => $value) {
                echo "<tr><th>" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . "</th><td>" . htmlspecialchars(is_array($value) ? implode(", ", $value) : $value, ENT_QUOTES, 'UTF-8') . "</td></tr>";
            }
            echo "</table>";
            echo "</div>";

            // Mostrar el botón para eliminar los metadatos
            echo "<form action='procesar.php' method='POST'>";
            echo "<input type='hidden' name='delete' value='1'>";
            echo "<input type='hidden' name='filePath' value='" . htmlspecialchars($uploadFile, ENT_QUOTES, 'UTF-8') . "'>";
            echo "<button type='submit'>Eliminar Metadatos</button>";
            echo "</form>";
        } else {
            die("Error al obtener metadatos.");
        }
    } else {
        // Segunda parte: eliminar metadatos
        if (!isset($_POST['filePath'])) {
            die("Archivo no encontrado.");
        }

        $uploadFile = $_POST['filePath'];
        $fileExtension = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $fileName = basename($uploadFile);
        $cleanedFile = $cleanedDir . $fileName;

        // Eliminar los metadatos con ExifTool
        $cmd = "exiftool -all= -o " . escapeshellarg($cleanedFile) . " " . escapeshellarg($uploadFile);
        exec($cmd, $output, $result);

        // Verificar si ExifTool se ejecutó correctamente
        if ($result === 0) {
            // Eliminar el archivo original por seguridad
            unlink($uploadFile);

            // Proporcionar un enlace para descargar el archivo sin metadatos
            echo "<div class='success'>
                    <p>Metadatos eliminados correctamente. <a href='" . htmlspecialchars($cleanedFile, ENT_QUOTES, 'UTF-8') . "'>Descargar archivo sin metadatos</a></p>
                </div>";
        } else {
            die("Error al eliminar metadatos.");
        }
    }
}
