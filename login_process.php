<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "datican";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$entered_username = $_POST['username'];
$entered_password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username='$entered_username' AND password='$entered_password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Login successful
    $_SESSION['username'] = $entered_username;
    header("Location: welcome.php"); // Redirect to a welcome page
} else {
    // Login failed
    echo "Invalid username or password.";
}

$conn->close();
?>
