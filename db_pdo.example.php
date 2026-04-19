<?php
// ODBC Database Configuration for PDO (Used for kicking char only)
$db_host = 'YOUR_SERVER_IP';     // Server IP or localhost
$db_user = 'YOUR_DB_USER';       // SQL Server Username
$db_pass = 'YOUR_DB_PASSWORD';   // SQL Server Password
$db_name = 'PS_UserData';        // Database name

try {
    // Connect using PDO with ODBC Driver
    $pdoConn = new PDO("odbc:Driver={SQL Server};Server=$db_host;Database=$db_name", $db_user, $db_pass);
    $pdoConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("PDO Database connection failed: " . $e->getMessage());
}
?>
