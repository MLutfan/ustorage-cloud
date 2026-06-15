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
    $filepath = '../trash/' . $file['filename'];

    // Hapus file secara fisik dari server selamanya
    if (file_exists($filepath)) { unlink($filepath); }
    
    // Hapus data dari database
    $conn->query("DELETE FROM files WHERE id = '$file_id'");
    catatLog($conn, $username, 'ADMIN', 'HARD DELETE', $file['original_name'], 'BERHASIL (Permanen)');
}
header("Location: ../admin.php");
exit;
?>