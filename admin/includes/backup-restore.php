<?php

/** Create a SQL snapshot using the same format accepted by backupRestoreStatements(). */
function backupCreateSnapshot(PDO $conn, string $directory, ?int $createdBy): array
{
    if (!is_dir($directory) || !is_writable($directory)) {
        throw new RuntimeException('The backup directory is not writable.');
    }

    $filename = '4to9_backup_' . date('Y_m_d_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
    $path = $directory . DIRECTORY_SEPARATOR . $filename;
    $tables = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $sql = "-- Database Backup: restaurant_4to9\n";
    $sql .= '-- Generated: ' . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ----------------------------------------\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        if (!preg_match('/^[a-z][a-z0-9_]*$/i', $table)) {
            throw new RuntimeException('The database contains a table name that cannot be backed up safely.');
        }
        $sql .= "-- Table structure for table `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $sql .= $create[1] . ";\n\n";
        $sql .= "-- Data for table `$table`\n";
        $rows = $conn->query("SELECT * FROM `$table`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = [];
            foreach ($row as $value) {
                $values[] = $value === null ? 'NULL' : $conn->quote((string)$value);
            }
            $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
        }
        $sql .= "\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    if (file_put_contents($path, $sql, LOCK_EX) === false) {
        throw new RuntimeException('The backup file could not be written.');
    }
    try {
        $size = filesize($path);
        $stmt = $conn->prepare('INSERT INTO backups (filename, file_size, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$filename, $size, $createdBy]);
        return ['id' => (int)$conn->lastInsertId(), 'filename' => $filename, 'path' => $path, 'size' => $size];
    } catch (Throwable $exception) {
        @unlink($path);
        throw $exception;
    }
}

/** Parse the app's own SQL backup format before running any statement. */
function backupReadRestoreStatements(string $path): array
{
    $size = @filesize($path);
    if ($size === false || $size < 100 || $size > 100 * 1024 * 1024) {
        throw new InvalidArgumentException('This backup file is missing or too large to restore.');
    }
    $sql = file_get_contents($path);
    if ($sql === false || !str_starts_with($sql, '-- Database Backup: restaurant_4to9')) {
        throw new InvalidArgumentException('This file is not a 4 TO 9 database backup.');
    }

    $statements = [];
    $statement = '';
    $quote = null;
    $comment = false;
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        if ($comment) {
            if ($char === "\n") {
                $comment = false;
                $statement .= "\n";
            }
            continue;
        }
        if ($quote !== null) {
            $statement .= $char;
            if ($char === '\\' && $quote !== '`' && $i + 1 < $length) {
                $statement .= $sql[++$i];
            } elseif ($char === $quote) {
                if ($i + 1 < $length && $sql[$i + 1] === $quote) {
                    $statement .= $sql[++$i];
                } else {
                    $quote = null;
                }
            }
            continue;
        }
        if ($char === '-' && $i + 2 < $length && $sql[$i + 1] === '-' && ctype_space($sql[$i + 2])) {
            $comment = true;
            $i++;
            continue;
        }
        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $statement .= $char;
            continue;
        }
        if ($char === ';') {
            $part = trim($statement);
            if ($part !== '') {
                $statements[] = $part;
            }
            $statement = '';
            continue;
        }
        $statement .= $char;
    }
    if ($quote !== null || trim($statement) !== '') {
        throw new InvalidArgumentException('The backup SQL is incomplete.');
    }

    if (count($statements) < 2 ||
        !preg_match('/^SET FOREIGN_KEY_CHECKS\s*=\s*0$/i', $statements[0]) ||
        !preg_match('/^SET FOREIGN_KEY_CHECKS\s*=\s*1$/i', end($statements))) {
        throw new InvalidArgumentException('The backup SQL has an unexpected format.');
    }

    $created = [];
    $dropped = [];
    foreach (array_slice($statements, 1, -1) as $part) {
        if (preg_match('/^DROP TABLE IF EXISTS `([a-z][a-z0-9_]*)`$/i', $part, $match)) {
            $dropped[$match[1]] = true;
        } elseif (preg_match('/^CREATE TABLE `([a-z][a-z0-9_]*)`\s*\(/is', $part, $match)) {
            $created[$match[1]] = true;
        } elseif (preg_match('/^INSERT INTO `([a-z][a-z0-9_]*)` VALUES\s*\(/is', $part, $match)) {
            if (!isset($created[$match[1]])) {
                throw new InvalidArgumentException('The backup SQL contains an unexpected table insert.');
            }
        } else {
            throw new InvalidArgumentException('The backup SQL contains an unexpected statement.');
        }
    }
    foreach (['users', 'restaurant_tables', 'bookings', 'menu_categories', 'menu_items', 'restaurant_settings', 'gallery', 'backups'] as $table) {
        if (!isset($dropped[$table], $created[$table])) {
            throw new InvalidArgumentException('The backup is missing a required restaurant table.');
        }
    }
    if (array_diff_key($created, $dropped) || array_diff_key($dropped, $created)) {
        throw new InvalidArgumentException('The backup has mismatched table definitions.');
    }
    return $statements;
}

/** Restore only to the PDO connection supplied by the caller. */
function backupRestoreStatements(PDO $target, array $statements): void
{
    try {
        foreach ($statements as $statement) {
            $target->exec($statement);
        }
    } finally {
        $target->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
