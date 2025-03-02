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



$conn = new mysqli('localhost', 'root', '', 'rezervare_filme');

if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

// update with POST form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $tableName = $_POST['table'];
    $primaryKeyColumn = $_POST['primary_key'];
    $primaryKeyValue = $_POST['primary_key_value'];

    //  UPDATE query
    $updateQuery = "UPDATE $tableName SET ";
    $updates = [];
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['update', 'table', 'primary_key', 'primary_key_value'])) {
            $updates[] = "$key = '" . $conn->real_escape_string($value) . "'";
        }
    }
    $updateQuery .= implode(', ', $updates) . " WHERE $primaryKeyColumn = '$primaryKeyValue'";

    if ($conn->query($updateQuery)) {
        echo "<p style='color: green;'>Rândul a fost actualizat cu succes în tabela $tableName!</p>";
    } else {
        echo "<p style='color: red;'>Eroare la actualizarea rândului: " . $conn->error . "</p>";
    }
}

// data deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $tableName = $_POST['table'];
    $primaryKeyColumn = $_POST['primary_key'];
    $primaryKeyValue = $_POST['primary_key_value'];

    // DELETE query
    $deleteQuery = "DELETE FROM $tableName WHERE $primaryKeyColumn = '$primaryKeyValue'";

    if ($conn->query($deleteQuery)) {
        echo "<p style='color: green;'>Rândul a fost șters cu succes din tabela $tableName!</p>";
    } else {
        echo "<p style='color: red;'>Eroare la ștergerea rândului: " . $conn->error . "</p>";
    }
}

// add of data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $tableName = $_POST['table'];

    $columns = [];
    $values = [];
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['add', 'table'])) {
            $columns[] = $key;
            $values[] = "'" . $conn->real_escape_string($value) . "'";
        }
    }

    // insert query 
    $insertQuery = "INSERT INTO $tableName (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";

    if ($conn->query($insertQuery)) {
        echo "<p style='color: green;'>Rândul a fost adăugat cu succes în tabela $tableName!</p>";
    } else {
        echo "<p style='color: red;'>Eroare la adăugarea rândului: " . $conn->error . "</p>";
    }
}

// function to show the content
function displayTableContent($conn, $tableName) {
    // Obtain of the primary key
    $primaryKeyQuery = "SHOW KEYS FROM $tableName WHERE Key_name = 'PRIMARY'";
    $primaryKeyResult = $conn->query($primaryKeyQuery);
    $primaryKeyColumn = $primaryKeyResult->fetch_assoc()['Column_name'];

    $query = "SELECT * FROM $tableName";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo "<div class='table-container'>";
        echo "<h3>$tableName</h3>";
        echo "<table>";
        echo "<tr>";

        // Show headers
        while ($field = $result->fetch_field()) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "<th>Actions</th>";
        echo "</tr>";

        // Show rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }

            // Edit and Delete buttons
            echo "<td>
                    <form method='POST' action='edit.php' style='display: inline;'>
                        <input type='hidden' name='table' value='$tableName'>
                        <input type='hidden' name='primary_key' value='$primaryKeyColumn'>
                        <input type='hidden' name='primary_key_value' value='{$row[$primaryKeyColumn]}'>
                        <button type='submit'>Edit</button>
                    </form>
                    <form method='POST' style='display: inline;'>
                        <input type='hidden' name='delete' value='1'>
                        <input type='hidden' name='table' value='$tableName'>
                        <input type='hidden' name='primary_key' value='$primaryKeyColumn'>
                        <input type='hidden' name='primary_key_value' value='{$row[$primaryKeyColumn]}'>
                        <button type='submit' style='background-color: red;'>Delete</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // form for adding
        echo "<form method='POST' style='margin-top: 20px;'>
                <h4>Add a new row in $tableName</h4>";
        $result->field_seek(0);
        while ($field = $result->fetch_field()) {
            echo "<label>" . htmlspecialchars($field->name) . "</label>";
            echo "<input type='text' name='" . htmlspecialchars($field->name) . "' required>";
        }
        echo "<input type='hidden' name='add' value='1'>
              <input type='hidden' name='table' value='$tableName'>
              <button type='submit' style='background-color: green;'>Add</button>
              </form>";

        echo "</div>";
    } else {
        echo "<p class='no-data'>There is no data in $tableName.</p>";
    }
}

// all tables
$query = "SHOW TABLES";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="ro">
<head>
<a href="queries.php">Queries with Join</a>
<a href="advanced_queries.php">Queries with Subqueries</a>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h1 {
            color: #0056b3;
            margin-top: 20px;
        }
        h3 {
            color: #333;
            text-align: center;
        }
        .table-container {
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            margin: 0 auto;
            width: 100%;
            background-color: #ffffff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #0056b3;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .no-data {
            color: #666;
            font-style: italic;
            text-align: center;
        }
        a {
            text-decoration: none;
            color: white;
            background-color: #0056b3;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px;
            display: inline-block;
        }
        a:hover {
            background-color: #003f7f;
        }
        button {
            padding: 5px 10px;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #003f7f;
        }
    </style>
</head>
<body>
    <h1>Welcome!</h1>
    <h2>Information related to Reservations Clients and Cinemas:</h2>
    <?php
    if ($result) {
        while ($row = $result->fetch_array()) {
            displayTableContent($conn, $row[0]);
        }
    } else {
        echo "<p class='no-data'>There are no tables in the database.</p>";
    }
    ?>
    <a href="logout.php">Disconnect</a>
</body>
</html>

<?php
$conn->close();
?>
