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
    die("Conexiune eșuată: " . $conn->connect_error);
}

// parameters pass from the form
$film = isset($_GET['film']) ? $conn->real_escape_string($_GET['film']) : '';
$cinema = isset($_GET['cinema']) ? $conn->real_escape_string($_GET['cinema']) : '';
$capacitate = isset($_GET['capacitate']) ? (int)$_GET['capacitate'] : 0;

// function for query execution
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

        // data show
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-data'>tehre is no data for this query.</p>";
    }
    echo "</div>";
}

// Queries
$queries = [
    [
        "query" => "SELECT clienti.nume AS Nume_Client, clienti.prenume AS Prenume_Client, film.nume_film AS Film, sala.nr_sala AS Sala, rezervare.data AS Data_Rezervare
                    FROM rezervare
                    JOIN clienti ON rezervare.ID_client = clienti.ID_client
                    JOIN sali_filme ON rezervare.ID_sali_filme = sali_filme.ID_sali_filme
                    JOIN film ON sali_filme.ID_film = film.ID_film
                    JOIN sala ON sali_filme.ID_sala = sala.ID_sala
                    WHERE (film.nume_film LIKE '%$film%' OR '$film' = '')
                    ORDER BY rezervare.data DESC;",
        "description" => "Reservations made by customers, including details about movies and theaters."
    ],
    [
        "query" => "SELECT cinema.nume_cinema AS Cinema, sala.nr_sala AS Sala, film.nume_film AS Film
                    FROM sali_filme
                    JOIN sala ON sali_filme.ID_sala = sala.ID_sala
                    JOIN cinema ON sala.ID_cinema = cinema.ID_cinema
                    JOIN film ON sali_filme.ID_film = film.ID_film
                    WHERE (cinema.nume_cinema LIKE '%$cinema%' OR '$cinema' = '')
                    ORDER BY cinema.nume_cinema;",
        "description" => "The movies that are playing in each cinema."
    ],
    [
        "query" => "SELECT cinema.nume_cinema AS Cinema, SUM(sala.capacitate) AS Capacitate_Totala
                    FROM sala
                    JOIN cinema ON sala.ID_cinema = cinema.ID_cinema
                    GROUP BY cinema.nume_cinema
                    HAVING Capacitate_Totala >= $capacitate
                    ORDER BY Capacitate_Totala DESC;",
        "description" => "The total seating capacity of the rooms for each cinema."
    ],
    [
    "query" => "SELECT clienti.nume AS Nume_Client, clienti.prenume AS Prenume_Client, film.nume_film AS Film
    FROM rezervare
    JOIN clienti ON rezervare.ID_client = clienti.ID_client
    JOIN sali_filme ON rezervare.ID_sali_filme = sali_filme.ID_sali_filme
    JOIN film ON sali_filme.ID_film = film.ID_film;",
"description" => "Customers who have reserved movies."
],
[
"query" => "SELECT film.nume_film AS Film, sala.nr_sala AS Sala
    FROM sali_filme
    JOIN sala ON sali_filme.ID_sala = sala.ID_sala
    JOIN film ON sali_filme.ID_film = film.ID_film;",
"description" => "The movies and the theaters where they are screened."
],
[
"query" => "SELECT film.nume_film AS Film, film.Tip_film AS Tip, film.durata AS Durata, sali_filme.ID_sali_filme AS ID_sala
    FROM film
    join sali_filme on film.ID_film=sali_filme.ID_film;",
"description" => "3D or 4D movies and their duration along with the ID of the room where they are screened."
],

];

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interogări cu JOIN</title>
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
        .no-data {
            color: #999;
            font-style: italic;
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
    <h1>Queries with Join</h1>
    <form method="GET" action="">
        <label for="film">Film:</label>
        <input type="text" name="film" id="film" placeholder="Insert the film name">

        <label for="cinema">Cinema:</label>
        <input type="text" name="cinema" id="cinema" placeholder="Insert the cinema name">

        <label for="capacitate">Minumum capacity:</label>
        <input type="number" name="capacitate" id="capacitate" placeholder="insert minimum capacity">

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
