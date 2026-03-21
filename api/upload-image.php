<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content-repo.php';

require_auth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$key = trim((string) ($_POST['key'] ?? ''));

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No se recibió imagen']);
    exit;
}

$file = $_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Error al subir la imagen']);
    exit;
}

if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'La imagen supera 5MB']);
    exit;
}

$tmpPath = (string) ($file['tmp_name'] ?? '');
$originalName = (string) ($file['name'] ?? '');

$allowedByImageType = [
    IMAGETYPE_JPEG => ['extension' => 'jpg', 'mime' => 'image/jpeg'],
    IMAGETYPE_PNG => ['extension' => 'png', 'mime' => 'image/png'],
    IMAGETYPE_WEBP => ['extension' => 'webp', 'mime' => 'image/webp'],
];

$detectedType = function_exists('exif_imagetype') ? @exif_imagetype($tmpPath) : false;
$detected = $detectedType !== false && isset($allowedByImageType[$detectedType])
    ? $allowedByImageType[$detectedType]
    : null;

if ($detected === null) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = is_resource($finfo) ? (string) finfo_file($finfo, $tmpPath) : '';
    if (is_resource($finfo)) {
        finfo_close($finfo);
    }

    $allowedByMime = [
        'image/jpeg' => ['extension' => 'jpg', 'mime' => 'image/jpeg'],
        'image/png' => ['extension' => 'png', 'mime' => 'image/png'],
        'image/webp' => ['extension' => 'webp', 'mime' => 'image/webp'],
    ];

    $detected = $allowedByMime[$mime] ?? null;
}

if ($detected === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El archivo no es una imagen válida. Solo se aceptan JPG, PNG o WEBP.']);
    exit;
}

$uploadsDir = dirname(__DIR__) . '/public/uploads';
if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo crear directorio de subidas']);
    exit;
}

$timestamp = (new DateTimeImmutable())->format('YmdHis');
$random = bin2hex(random_bytes(6));
$filename = sprintf('img_%s_%s.%s', $timestamp, $random, $detected['extension']);
$destination = $uploadsDir . '/' . $filename;

if (!move_uploaded_file($tmpPath, $destination)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la imagen']);
    exit;
}

$publicUrl = '/public/uploads/' . $filename;

if ($key !== '') {
    $currentContent = read_content_file();
    set_value_by_path($currentContent, $key, [
        'source_type' => 'upload',
        'value' => $publicUrl,
    ]);

    if (!save_content_file($currentContent)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo persistir el contenido']);
        exit;
    }
}

echo json_encode([
    'ok' => true,
    'key' => $key,
    'url' => $publicUrl,
    'name' => $filename,
    'mime' => $detected['mime'],
]);
