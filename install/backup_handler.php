<?php
/**
 * VMS Database Backup Handler - Fixed Version
 */
session_start();

if (!isset($_SESSION['install_db'])) {
    die("دسترسی غیرمجاز! ابتدا مراحل نصب را طی کنید.");
}

$db_info = $_SESSION['install_db'];

try {
    $pdo = new PDO("mysql:host={$db_info['db_host']};dbname={$db_info['db_name']};charset=utf8mb4", $db_info['db_user'], $db_info['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $return = "-- VMS Project SQL Backup\n";
    $return .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $return .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tables = array();
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        // ساختار جدول
        $return .= "DROP TABLE IF EXISTS `$table`;\n";
        $row2 = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $return .= $row2[1] . ";\n\n";

        // داده‌های جدول (بدون عبارت text-end اضافی)
        $result = $pdo->query("SELECT * FROM `$table` ");
        $num_fields = $result->columnCount();

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                if (isset($row[$j])) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= 'NULL';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
        $return .= "\n\n\n";
    }

    $return .= "SET FOREIGN_KEY_CHECKS=1;";

    $fileName = 'vms_backup_' . date('Y-m-d_H-i') . '.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
    
    echo $return;
    exit;

} catch (PDOException $e) {
    die("خطا در تهیه نسخه پشتیبان: " . $e->getMessage());
}