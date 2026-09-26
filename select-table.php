<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$query = http_build_query(array_filter([
    'date' => trim((string)($_GET['date'] ?? '')),
    'time' => trim((string)($_GET['time'] ?? '')),
    'guests' => trim((string)($_GET['guests'] ?? ''))
]));
redirect('tables.php' . ($query ? '?' . $query : ''));
