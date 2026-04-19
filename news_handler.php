<?php
session_start();
require_once 'db.php';

// Initialization: Create Table if it doesn't exist
$createTableSQL = "
IF NOT EXISTS (SELECT * FROM PS_UserData.INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Web_News')
BEGIN
    CREATE TABLE PS_UserData.dbo.Web_News (
        NewsID INT IDENTITY(1,1) PRIMARY KEY,
        Title NVARCHAR(255) NOT NULL,
        Content NVARCHAR(MAX) NOT NULL,
        Author NVARCHAR(50) NOT NULL,
        CreatedAt DATETIME DEFAULT GETDATE(),
        IsHidden BIT DEFAULT 0
    )
END
ELSE
BEGIN
    -- Upgrade existing columns to NVARCHAR if they are still VARCHAR
    IF (SELECT DATA_TYPE FROM PS_UserData.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Web_News' AND COLUMN_NAME = 'Content') = 'varchar'
        ALTER TABLE PS_UserData.dbo.Web_News ALTER COLUMN Content NVARCHAR(MAX) NOT NULL;
    IF (SELECT DATA_TYPE FROM PS_UserData.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Web_News' AND COLUMN_NAME = 'Title') = 'varchar'
        ALTER TABLE PS_UserData.dbo.Web_News ALTER COLUMN Title NVARCHAR(255) NOT NULL;
    IF (SELECT DATA_TYPE FROM PS_UserData.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Web_News' AND COLUMN_NAME = 'Author') = 'varchar'
        ALTER TABLE PS_UserData.dbo.Web_News ALTER COLUMN Author NVARCHAR(50) NOT NULL;
END
";
@odbc_exec($conn, $createTableSQL);

// Security: Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['user'];

// Security: Check Admin status (16)
$user_sql = "SELECT Status, UserUID FROM PS_UserData.dbo.Users_Master WHERE UserID = ?";
$user_stmt = odbc_prepare($conn, $user_sql);
odbc_execute($user_stmt, [$username]);
$user_data = odbc_fetch_array($user_stmt);
$userStatus = (int)$user_data['Status'];

if ($userStatus != 16) {
    header("Location: index.php");
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'create':
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if ($title && $content) {
            // Server-side limit: 45 characters for title
            $title = mb_substr($title, 0, 45);
            $isHidden = isset($_POST['is_hidden']) ? 1 : 0;
            
            $query = odbc_prepare(
                $conn,
                "INSERT INTO PS_UserData.dbo.Web_News (Title, Content, Author, CreatedAt, IsHidden) 
                 VALUES (?, ?, ?, GETDATE(), ?)"
            );
            odbc_execute($query, [$title, $content, $username, $isHidden]);
        }
        header("Location: index.php");
        break;

    case 'edit':
        $newsID = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if ($newsID && $title && $content) {
            // Server-side limit: 45 characters for title
            $title = mb_substr($title, 0, 45);
            $query = odbc_prepare(
                $conn,
                "UPDATE PS_UserData.dbo.Web_News 
                 SET Title = ?, Content = ? 
                 WHERE NewsID = ?"
            );
            odbc_execute($query, [$title, $content, $newsID]);
        }
        header("Location: index.php");
        break;

    case 'delete':
        $newsID = isset($_POST['news_id']) ? (int)$_POST['news_id'] : (isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0);
        if ($newsID) {
            $query = odbc_prepare($conn, "DELETE FROM PS_UserData.dbo.Web_News WHERE NewsID = ?");
            odbc_execute($query, [$newsID]);
        }
        header("Location: index.php");
        break;

    case 'toggle_visibility':
        $newsID = isset($_POST['news_id']) ? (int)$_POST['news_id'] : (isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0);
        if ($newsID) {
            $query = odbc_prepare(
                $conn,
                "UPDATE PS_UserData.dbo.Web_News 
                 SET IsHidden = CASE WHEN IsHidden = 1 THEN 0 ELSE 1 END 
                 WHERE NewsID = ?"
            );
            odbc_execute($query, [$newsID]);
        }
        header("Location: index.php");
        break;

    case 'upload_image':
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            $allowed_types = ['image/png', 'image/jpeg', 'image/webp'];
            
            if (in_array($file['type'], $allowed_types)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('news_') . '.' . $ext;
                $target_dir = 'uploads/news/';
                $target_file = $target_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    echo json_encode(['url' => $target_file]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to move uploaded file.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid file type.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'No file uploaded.']);
        }
        exit;
        break;

    default:
        header("Location: index.php");
        break;
}
exit;
?>
