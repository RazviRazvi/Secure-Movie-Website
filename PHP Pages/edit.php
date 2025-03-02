<?php
session_start();

// Prevent Session Fixation
session_regenerate_id(true);

// Secure Cookie Settings
ini_set('session.cookie_httponly', 1); // Prevent JavaScript from accessing session cookies (protects against XSS)
ini_set('session.cookie_secure', 1);   // Ensure cookies are only sent over HTTPS
ini_set('session.use_only_cookies', 1); // Prevent session ID from appearing in URLs (reduces risk of exposure)

//Session Timeout
$timeout = 1800; 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Redirect user to login page after timeout
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

// Restrict Session Access to Current User
if (!isset($_SESSION['IP_ADDRESS'])) {
    $_SESSION['IP_ADDRESS'] = $_SERVER['REMOTE_ADDR']; // Store user's IP
}
if ($_SESSION['IP_ADDRESS'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Destroy session if IP changes
    exit();
}


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tableName = $_POST['table'];
    $primaryKey = $_POST['primary_key'];
    $primaryKeyValue = $_POST['primary_key_value'];

    $conn = new mysqli('localhost', 'root', '', 'rezervare_filme');

    if ($conn->connect_error) {
        die("Conexiune eșuată: " . $conn->connect_error);
    }

    $query = "SELECT * FROM $tableName WHERE $primaryKey = '$primaryKeyValue'";

    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        ?>
        <!DOCTYPE html>
        <html lang="ro">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Edit row</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f9;
                    color: #333;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                form {
                    background-color: white;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                    width: 400px;
                }
                h1 {
                    text-align: center;
                    color: #0056b3;
                    margin-bottom: 20px;
                }
                label {
                    font-weight: bold;
                    display: block;
                    margin-top: 10px;
                }
                input[type="text"] {
                    width: 100%;
                    padding: 8px;
                    margin-top: 5px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                button {
                    width: 100%;
                    padding: 10px;
                    margin-top: 20px;
                    background-color: #0056b3;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #003f7f;
                }
                .cancel-btn {
                    background-color: #ccc;
                    color: #333;
                }
                .cancel-btn:hover {
                    background-color: #bbb;
                }
            </style>
        </head>
        <body>
            <form method="POST" action="dashboard.php">
                <h1>Edit Table From <?php echo htmlspecialchars($tableName); ?></h1>
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($tableName); ?>">
                <input type="hidden" name="primary_key" value="<?php echo htmlspecialchars($primaryKey); ?>">
                <input type="hidden" name="primary_key_value" value="<?php echo htmlspecialchars($primaryKeyValue); ?>">

                <?php
                foreach ($row as $key => $value) {
                    echo "<label for='$key'>" . htmlspecialchars($key) . "</label>";
                    echo "<input type='text' name='$key' id='$key' value='" . htmlspecialchars($value) . "'>";
                }
                ?>

                <button type="submit" name="update">Save</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
            </form>
        </body>
        </html>
        <?php
    } else {
        echo "<p>Row doesn't exist!</p>";
    }

    $conn->close();
}
?>
