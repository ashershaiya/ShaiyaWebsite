<?php
// Set Timezone for server time
date_default_timezone_set('Europe/Sofia');

// ODBC Database Configuration
$db_host = 'YOUR_SERVER_IP';     // Server IP or localhost
$db_user = 'YOUR_DB_USER';       // SQL Server Username
$db_pass = 'YOUR_DB_PASSWORD';   // SQL Server Password
$db_name = 'PS_UserData';        // Database name

// Build connection string
$dsn = "Driver={SQL Server};Server=$db_host;Database=$db_name;";

// Connect using odbc_connect
$conn = odbc_connect($dsn, $db_user, $db_pass);

if (!$conn) {
    die("Database connection failed: " . odbc_errormsg());
}

// Ensure we can read large content
ini_set('odbc.defaultlrl', '20M');

// Ensure Web_PointHistory table exists
$checkTableSql = "IF NOT EXISTS (SELECT * FROM PS_UserData.sys.tables WHERE name = 'Web_PointHistory')
BEGIN
    CREATE TABLE PS_UserData.dbo.Web_PointHistory (
        RowID INT IDENTITY(1,1) PRIMARY KEY,
        UserID VARCHAR(32) NOT NULL,
        PointsAdded INT NOT NULL,
        Reason VARCHAR(255) NULL,
        GM_Account VARCHAR(32) NOT NULL,
        [Date] DATETIME DEFAULT GETDATE()
    )
END";
odbc_exec($conn, $checkTableSql);
?>
