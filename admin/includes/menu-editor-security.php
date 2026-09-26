<?php

function menuEditorCsrfToken()
{
    if (empty($_SESSION['menu_editor_csrf'])) {
        $_SESSION['menu_editor_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['menu_editor_csrf'];
}

function menuEditorVerifyCsrf()
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(menuEditorCsrfToken(), $submitted)) {
        throw new InvalidArgumentException('Your form expired. Refresh the page and try again.');
    }
}

function menuEditorText($key, $maximum, $required = false)
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Please enter valid form details.');
    }
    $value = trim($value);
    if (($required && $value === '') || strlen($value) > $maximum) {
        throw new InvalidArgumentException('Please check the ' . str_replace('_', ' ', $key) . ' field.');
    }
    return $value;
}

function menuEditorId($key)
{
    $value = $_POST[$key] ?? null;
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || $id === null) {
        throw new InvalidArgumentException('Please choose a valid record.');
    }
    return $id;
}

function menuEditorPrice()
{
    $price = menuEditorText('price', 11, true);
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price) || (float)$price <= 0) {
        throw new InvalidArgumentException('Enter a price above zero with up to two decimal places.');
    }
    return $price;
}

function menuEditorUploadImage($directory)
{
    if (!isset($_FILES['image'])) {
        return null;
    }
    $file = $_FILES['image'];
    if (!is_array($file)) {
        throw new InvalidArgumentException('Choose a valid image file.');
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!is_array($file) || ($file['error'] ?? 1) !== UPLOAD_ERR_OK ||
        !isset($file['size'], $file['tmp_name']) || $file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024 ||
        !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Choose an image smaller than 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new InvalidArgumentException('Upload a JPG, PNG, or WebP image.');
    }

    $path = __DIR__ . '/../../uploads/' . $directory;
    if (!is_dir($path) && !mkdir($path, 0755, true)) {
        throw new RuntimeException('The image folder could not be created.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $path . '/' . $name)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return $name;
}

function menuEditorDeleteUpload($directory, $filename)
{
    if (!in_array($directory, ['menu', 'categories', 'gallery'], true) ||
        !is_string($filename) ||
        !preg_match('/^[a-f0-9]{32}\.(?:jpg|png|webp)$/D', $filename)) {
        return;
    }

    $path = __DIR__ . '/../../uploads/' . $directory . '/' . $filename;
    if (is_file($path) && !unlink($path)) {
        error_log('Could not remove unused menu image: ' . $path);
    }
}
