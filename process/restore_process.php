<?php
session_start();
require_once '../config/database.php';
require_once 'logger.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) { header("Location: ../index.php"); exit; }

$file_id = intval($_GET['id']);
$username = $_SESSION['username'];
$query = "SELECT * FROM files WHERE id = '$file_id' AND status = 'trash'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $file = $result->fetch_assoc();
    $source = '../trash/' . $file['filename'];
    $destination = '../uploads/' . $file['filename'];

    if (rename($source, $destination)) {
        $conn->query("UPDATE files SET status = 'active' WHERE id = '$file_id'");
        catatLog($conn, $username, 'ADMIN', 'RESTORE', $file['original_name'], 'BERHASIL');
    }
}
header("Location: ../admin.php");
exit;
?>