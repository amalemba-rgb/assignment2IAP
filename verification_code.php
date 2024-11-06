<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "mydatabase");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
} 

if (isset($_GET['code'])) {
    $two_fa_code = mysqli_real_escape_string($con, $_GET['code']); // Sanitize input

    // Check if the code exists in the database
    $query = "SELECT * FROM assign WHERE two_fa_code='$two_fa_code' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update user status to verified
        $update_query = "UPDATE assign SET email_verified=1 WHERE two_fa_code='$two_fa_code'";
        $update_result = mysqli_query($con, $update_query);

        if ($update_result) {
            echo "Email verification successful. You can now log in.";
        } else {
            echo "Email verification failed: " . mysqli_error($con); // Show the error
        }
    } else {
        echo "Invalid verification code.";
    }
} else {
    echo "No verification code provided.";
}

// Close the database connection
mysqli_close($con);
?>
