<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$lastName = $email = $phone = $password = $confirmPassword = "";

$host = 'localhost';
$dbname = 'motionmate';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lastName = trim($_POST['Last_Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $password = trim($_POST['Password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    if (empty($lastName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!preg_match("/^\d{11}$/", $phone)) {
        $message = "Phone number must be 11 digits.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $message = "Email or phone number already exists.";
        } else {
            $sql = "INSERT INTO users (last_name, email, phone, password) VALUES (:last_name, :email, :phone, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $message = "There was an error registering the user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <h2>Create an Account</h2>
            <?php if (!empty($message)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <div class="input-field">
                <input type="text" id="Last_Name" name="Last_Name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                <label for="Last_Name">Last Name</label>
            </div>
            <div class="input-field">
                <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label for="Email">Email</label>
            </div>
            <div class="input-field">
                <input type="text" id="Phone" name="Phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                <label for="Phone">Phone (11 digits)</label>
            </div>
            <div class="input-field">
                <input type="password" id="Password" name="Password" required>
                <label for="Password">Password</label>
            </div>
            <div class="input-field">
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <label for="confirmPassword">Confirm Password</label>
            </div>
            <button type="submit">Register</button>
            <div class="login-link">
                <p>Already have an account? <a href="Login.php">Login here</a></p>
            </div>
        </form>
    </div>
</body>
</html>
