
<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Ensure the path is correct

// Database connection
$con = mysqli_connect("localhost", "root", "", "mydatabase");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create assign table if it doesn't exist
$tableCreationQuery = "CREATE TABLE IF NOT EXISTS assign (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,  /* Ensure email is unique */
    password VARCHAR(255) NOT NULL,
    two_fa_code VARCHAR(255),  
    email_verified TINYINT(1) DEFAULT 0,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

mysqli_query($con, $tableCreationQuery);

function sendVerificationEmail($username, $email, $two_fa_code) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elton.alemba@gmail.com'; 
        $mail->Password   = 'sjly hogc bvmk ejav';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('elton.alemba@gmail.com', 'Amalemba Elton');  
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body    = "<h1>Hi, $username</h1>
                          <p>Thank you for registering.</p>
                          <p>Please verify your email by clicking the link below:</p>
                          <a href='http://localhost/assignment2/verification_code.php?code=$two_fa_code'>Verify Email</a>";
        
        $mail->send();
        echo 'Verification email has been sent.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $two_fa_code = md5(rand());

    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM assign WHERE email = ?";
    $stmtCheck = $con->prepare($checkEmailQuery);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        echo "Email is already registered. Please use a different email.";
    } else {
        // prepared statements to insert user into the database
        $stmt = $con->prepare("INSERT INTO assign (username, email, password, two_fa_code, email_verified) VALUES (?, ?, ?, ?, ?)");
        $email_verified = 0; // Default value

        // Bind parameters
        $stmt->bind_param("ssssi", $username, $email, $password, $two_fa_code, $email_verified);

        // Execute and check for errors
        if ($stmt->execute()) {
            // Send verification email
            sendVerificationEmail($username, $email, $two_fa_code);
            echo "Registration successful. Please check your email to verify your account.";
        } else {
            echo "Registration failed: " . $stmt->error; // Print detailed error
        }

        $stmt->close();
    }

    $stmtCheck->close();
}

$con->close();
