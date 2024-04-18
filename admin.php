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

// Function to update claim status and add comments
function updateClaimStatus($conn, $claimId, $status, $comment) {
    // Implement your logic to update claim status and comments
    $comment = mysqli_real_escape_string($conn, $comment); // Escaping to prevent SQL injection
    $sql = "UPDATE claim_requests SET claim_status = '$status', admin_comment = '$comment' WHERE claim_id = '$claimId'";
    return $conn->query($sql);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["approveClaim"])) {
        // Approve the claim
        $claimId = $_POST["claimId"];
        $comment = $_POST["adminComment"];
        updateClaimStatus($conn, $claimId, 'Approved', $comment);
    } elseif (isset($_POST["discardClaim"])) {
        // Discard the claim
        $claimId = $_POST["claimId"];
        $comment = $_POST["adminComment"];
        updateClaimStatus($conn, $claimId, 'Discarded', $comment);
    }

    // Redirect to prevent form resubmission
    header("Location: admin.php");
    exit();
}

// Retrieve all pending claim requests from the database
$sqlPending = "SELECT * FROM claim_requests WHERE claim_status = 'Pending'";
$resultPending = $conn->query($sqlPending);

// Store pending claim requests in session for display
$_SESSION["pendingClaims"] = [];
if ($resultPending->num_rows > 0) {
    while ($rowPending = $resultPending->fetch_assoc()) {
        $_SESSION["pendingClaims"][] = $rowPending;
    }
}

// Retrieve all claim requests from the database
$sqlAll = "SELECT * FROM claim_requests";
$resultAll = $conn->query($sqlAll);

// Store all claim requests in session for display
$_SESSION["allClaims"] = [];
if ($resultAll->num_rows > 0) {
    while ($rowAll = $resultAll->fetch_assoc()) {
        $_SESSION["allClaims"][] = $rowAll;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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
    <h2>Welcome, Admin!</h2>
    <a href="logout.php">Logout</a>

    <?php if (isset($_SESSION["pendingClaims"]) && !empty($_SESSION["pendingClaims"])) { ?>
        <!-- Display Pending Claim Requests -->
        <h2>Pending Claim Requests</h2>
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
                    <th>Status</th>
                    <th>File</th>
                    <th>Action</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION["pendingClaims"] as $claim) { ?>
                    <tr>
                        <td><?php echo $claim["timestamp"]; ?></td>
                        <td><?php echo $claim["username"]; ?></td>
                        <td><?php echo $claim["claim_id"]; ?></td>
                        <td><?php echo $claim["claim_amount"]; ?></td>
                        <td><?php echo $claim["claim_description"]; ?></td>
                        <td><?php echo $claim["full_name"]; ?></td>
                        <td><?php echo $claim["bank"]; ?></td>
                        <td><?php echo $claim["account_number"]; ?></td>
                        <td><?php echo $claim["claim_status"]; ?></td>
                        <td>
                            <?php
                                $filePath = $claim["file_path"];
                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                
                                // Check if the file is an image
                                if (in_array($fileExtension, array("jpg", "jpeg", "png", "gif"))) {
                                    echo '<a href="' . $filePath . '" target="_blank">View Image</a>';
                                } else {
                                    echo '<a href="' . $filePath . '" target="_blank">Download File</a>';
                                }
                            ?>
                        </td>
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="claimId" value="<?php echo $claim["claim_id"]; ?>">
                                <input type="text" name="adminComment" placeholder="Admin Comment">
                                <button type="submit" name="approveClaim">Approve</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="claimId" value="<?php echo $claim["claim_id"]; ?>">
                                <input type="text" name="adminComment" placeholder="Admin Comment">
                                <button type="submit" name="discardClaim">Discard</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <!-- No Pending Claims Message -->
        <p>No pending claims found.</p>
    <?php } ?>

    <?php if (isset($_SESSION["allClaims"]) && !empty($_SESSION["allClaims"])) { ?>
        <!-- Display All Claim Requests -->
        <h2>All Claim Requests</h2>
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
                    <th>Status</th>
                    <th>File</th>
                    <th>Admin's Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION["allClaims"] as $claim) { ?>
                    <tr>
                        <td><?php echo $claim["timestamp"]; ?></td>
                        <td><?php echo $claim["username"]; ?></td>
                        <td><?php echo $claim["claim_id"]; ?></td>
                        <td><?php echo $claim["claim_amount"]; ?></td>
                        <td><?php echo $claim["claim_description"]; ?></td>
                        <td><?php echo $claim["full_name"]; ?></td>
                        <td><?php echo $claim["bank"]; ?></td>
                        <td><?php echo $claim["account_number"]; ?></td>
                        <td><?php echo $claim["claim_status"]; ?></td>
                        <td>
                            <?php
                                $filePath = $claim["file_path"];
                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                
                                // Check if the file is an image
                                if (in_array($fileExtension, array("jpg", "jpeg", "png", "gif"))) {
                                    echo '<a href="' . $filePath . '" target="_blank">View Image</a>';
                                } else {
                                    echo '<a href="' . $filePath . '" target="_blank">Download File</a>';
                                }
                            ?>
                        </td>
                        <td><?php echo $claim["admin_comment"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <!-- No Claim Requests Message -->
        <p>No claim requests found.</p>
    <?php } ?>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
