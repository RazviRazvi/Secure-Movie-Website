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
    die("Connection failed: " . $conn->connect_error);
}

// parameters from form
$cinema = isset($_GET['cinema']) && !empty($_GET['cinema']) ? $conn->real_escape_string($_GET['cinema']) : null;
$capacitate = isset($_GET['capacitate']) && !empty($_GET['capacitate']) ? (int)$_GET['capacitate'] : null;

// function for queries
function executeQuery($conn, $query, $description) {
    echo "<div class='query-container'>";
    echo "<h2>$description</h2>";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr>";

        // headers
        while ($field = $result->fetch_field()) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";

        // show of data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-data'>Nu există rezultate pentru această interogare.</p>";
    }
    echo "</div>";
}

// complex queries
$queries = [
    [
        "query" => "SELECT cinema.nume_cinema, sala.nr_sala, sala.capacitate
                    FROM sala
                    JOIN cinema ON sala.ID_cinema = cinema.ID_cinema
                    WHERE sala.capacitate = (
                        SELECT MAX(capacitate)
                        FROM sala s
                        WHERE s.ID_cinema = sala.ID_cinema
                    )
                    ORDER BY sala.capacitate DESC;",
        "description" => "The theaters with the largest capacity for each cinema."
    ],
    [
        "query" => "SELECT clienti.nume, clienti.prenume, COUNT(rezervare.ID_rezervare) AS Rezervari
                    FROM rezervare
                    JOIN clienti ON rezervare.ID_client = clienti.ID_client
                    WHERE rezervare.ID_sali_filme IN (
                        SELECT ID_sali_filme
                        FROM sali_filme
                        WHERE ID_film = (
                            SELECT ID_film
                            FROM film
                            WHERE nume_film = 'Avatar'
                        )
                    )
                    GROUP BY clienti.nume, clienti.prenume
                    ORDER BY Rezervari DESC;",
        "description" => "Customers who have made the most reservations for the movie 'Avatar'."
    ],
    [
        "query" => "SELECT film.nume_film, COUNT(sali_filme.ID_sala) AS Nr_Sali
                    FROM film
                    JOIN sali_filme ON film.ID_film = sali_filme.ID_film
                    GROUP BY film.nume_film
                    HAVING COUNT(sali_filme.ID_sala) > (
                        SELECT AVG(NrSali)
                        FROM (
                            SELECT COUNT(ID_sala) AS NrSali
                            FROM sali_filme
                            GROUP BY ID_film
                        ) AS Subquery
                    );",
        "description" => "Movies that are screened in more theaters than the average of all movies."
    ],
    [
        "query" => "SELECT clienti.nume, clienti.prenume, film.nume_film, rezervare.data AS Data_Rezervare
                    FROM rezervare
                    JOIN clienti ON rezervare.ID_client = clienti.ID_client
                    JOIN sali_filme ON rezervare.ID_sali_filme = sali_filme.ID_sali_filme
                    JOIN film ON sali_filme.ID_film = film.ID_film
                    WHERE rezervare.data = (
                        SELECT MAX(r.data)
                        FROM rezervare r
                        WHERE r.ID_client = rezervare.ID_client
                    )
                    ORDER BY rezervare.data DESC;",
        "description" => "The latest reservations made by each customer, including the reserved movie and the reservation date."
    ],
    
    [
        "query" => "SELECT cinema.nume_cinema, sala.nr_sala, sala.capacitate
                    FROM cinema
                    JOIN sala ON cinema.ID_cinema = sala.ID_cinema
                    WHERE (" . ($cinema ? "cinema.nume_cinema LIKE '%$cinema%'" : "1=1") . ")
                    AND (" . ($capacitate ? "sala.capacitate >= $capacitate" : "1=1") . ")
                    ORDER BY cinema.nume_cinema, sala.capacitate DESC;",
        "description" => "The cinemas and theaters that meet the specified criteria."
    ]
];

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interogări SQL Complexe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #0056b3;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
            margin: 20px 0 10px 0;
        }
        .query-container {
            margin-bottom: 40px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #0056b3;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            margin-right: 10px;
        }
        input {
            margin-right: 20px;
        }
        button {
            padding: 5px 10px;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #003f7f;
        }
        a {
            display: inline-block;
            margin: 20px 0;
            text-decoration: none;
            color: white;
            background-color: #0056b3;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
        }
        a:hover {
            background-color: #003f7f;
        }
    </style>
</head>
<body>
    <h1>Complex SQL Queries</h1>
    <form method="GET" action="">
        <label for="cinema">Cinema:</label>
        <input type="text" name="cinema" id="cinema" placeholder="Insert the name of the cinema">

        <label for="capacitate">Minimum capacity</label>
        <input type="number" name="capacitate" id="capacitate" placeholder="Insert minimum capacity">

        <button type="submit">Filter</button>
    </form>
    <?php
    foreach ($queries as $query) {
        executeQuery($conn, $query['query'], $query['description']);
    }
    ?>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
<?php
$conn->close();
?>
