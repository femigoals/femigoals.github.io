<?php
// Start the session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "datican";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to set the initial status to Pending
function setPendingStatus() {
    return "Pending";
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $claimAmount = $_POST["claimAmount"];
    $claimDescription = $_POST["claimDescription"];
    $fullName = $_POST["fullName"];
    $bank = $_POST["bank"];
    $accountNumber = $_POST["accountNumber"];

    // Set the initial status to Pending
    $claimStatus = setPendingStatus();

    // Assuming the username is stored in the session
    $username = isset($_SESSION["username"]) ? $_SESSION["username"] : "Guest";

    // Capture the time the claim was made
    $timestamp = date("Y-m-d H:i:s");

    // Handle file upload
    $targetDir = "uploads/"; // Specify your target directory
    $targetFile = $targetDir . basename($_FILES["claimFile"]["name"]);

    if (move_uploaded_file($_FILES["claimFile"]["tmp_name"], $targetFile)) {
        // File uploaded successfully, now store data in the database
        $filePath = $targetFile;

        // Store claim data in the database
        $sql = "INSERT INTO claim_requests (username, claim_amount, claim_description, full_name, bank, account_number, claim_status, timestamp, file_path) 
                VALUES ('$username', '$claimAmount', '$claimDescription', '$fullName', '$bank', '$accountNumber', '$claimStatus', '$timestamp', '$filePath')";
        $conn->query($sql);

        // Redirect to prevent form resubmission
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        echo "Error uploading file.";
    }
}

// Retrieve all claim requests for the logged-in user from the database
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $sql = "SELECT * FROM claim_requests WHERE username = '$username'";
    $result = $conn->query($sql);

    // Store claim requests in session for display
    $_SESSION["claimRequests"] = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $_SESSION["claimRequests"][] = $row;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h2 {
            margin-top: 20px;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 10px;
            text-align: left;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .thumbnail {
            max-width: 100px;
            max-height: 100px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <img src="images/datican_logo.png" alt="Description of your image" width="500" height="180">

    <h2>Welcome, <?php echo $username; ?>!</h2>
    <a href="logout.php">Logout</a>
    <?php if (isset($_SESSION["claimRequests"]) && !empty($_SESSION["claimRequests"])) { ?>

        <!-- Display All Claim Requests -->
        <h2>Your Claim Requests</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Username</th>
                    <th>Claim ID</th>
                    <th>Claim Amount</th>
                    <th>Claim Description</th>
                    <th>Full Name</th>
                    <th>Bank</th>
                    <th>Account Number</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Admin's Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION["claimRequests"] as $request) { ?>
                    <tr>
                        <td><?php echo $request["timestamp"]; ?></td>
                        <td><?php echo $request["username"]; ?></td>
                        <td><?php echo $request["claim_id"]; ?></td>
                        <td><?php echo $request["claim_amount"]; ?></td>
                        <td><?php echo $request["claim_description"]; ?></td>
                        <td><?php echo $request["full_name"]; ?></td>
                        <td><?php echo $request["bank"]; ?></td>
                        <td><?php echo $request["account_number"]; ?></td>
                        <td>
                            <?php
                                $filePath = $request["file_path"];
                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                
                                // Check if the file is an image
                                if (in_array($fileExtension, array("jpg", "jpeg", "png", "gif"))) {
                                    echo '<a href="' . $filePath . '" target="_blank"><img src="' . $filePath . '" alt="Thumbnail" class="thumbnail"></a>';
                                } else {
                                    echo '<a href="' . $filePath . '" target="_blank">' . $filePath . '</a>';
                                }
                            ?>
                        </td>
                        <td><?php echo $request["claim_status"]; ?></td>
                        <td><?php echo $request["admin_comment"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <!-- No Claim Requests Message -->
        <p>No claim requests found.</p>
    <?php } ?>
    <!-- Claim Form -->
    <h2>Claim Form</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <label for="claimAmount">Claim Amount:</label>
        <input type="number" name="claimAmount" required>
        <br>
        <label for="claimDescription">Claim Description:</label>
        <textarea name="claimDescription" rows="4" required></textarea>
        <br>
        <label for="fullName">Full Name:</label>
        <input type="text" name="fullName" required>
        <br>
        <label for="bank">Bank:</label>
        <input type="text" name="bank" required>
        <br>
        <label for="accountNumber">Account Number:</label>
        <input type="text" name="accountNumber" required>
        <br>
        <label for="claimFile">Upload File:</label>
        <input type="file" name="claimFile" accept="image/*,pdf/*,doc/*">
        <br>
        <button type="submit">Submit Claim</button>
    </form>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
