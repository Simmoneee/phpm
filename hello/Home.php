<?php
session_start();

$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['user']['name']) : 'Guest';

// Database connection
$servername = "localhost"; // Adjust to your database server
$usernameDB = "root"; // Adjust to your database username
$passwordDB = ""; // Adjust to your database password
$dbname = "motionmate"; // Name of your database

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetchCities($conn) {
    $sql = "SELECT * FROM cities";
    $result = $conn->query($sql);
    $cities = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cities[] = $row;
        }
    }
    return $cities;
}

$items = fetchCities($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-city'])) {
    $newCity = htmlspecialchars(trim($_POST['city-name'])); 

    if (!empty($newCity)) {
        $stmt = $conn->prepare("INSERT INTO cities (name) VALUES (?)");
        $stmt->bind_param("s", $newCity);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<p style='color: red;'>City name cannot be empty.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete-id'])) {
    $deleteId = intval($_GET['delete-id']);
    $stmt = $conn->prepare("DELETE FROM cities WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-city'])) {
    $updateId = intval($_POST['city-id']);
    $updatedName = htmlspecialchars(trim($_POST['city-name']));

    if (!empty($updatedName)) {
        $stmt = $conn->prepare("UPDATE cities SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $updatedName, $updateId);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<p style='color: red;'>City name cannot be empty.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotionMate: Your Safety, Our Priority</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <div class="top-box">
        <input type="text" placeholder="Search..." class="search-bar">
    </div>
    
    <div class="left-box">
        <h2>Motion Mate</h2>
        <h3>Your Safety, Our Priority</h3>
       
        <div class="alert-container">
            <?php if ($isLoggedIn): ?>
                <p>Welcome, <?php echo $username; ?>!</p>
                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <button onclick="window.location.href='Login.php'">Login</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <div class="manage-cities-box">
            <h2>Manage Cities</h2>
            <ul>
                <?php foreach ($items as $item): ?>
                    <li>
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                        <a href="?delete-id=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this city?');" class="delete-btn">Delete</a>
                        <a href="?edit-id=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3>Add City</h3>
            <form action="" method="post">
                <label for="city-name">City Name:</label>
                <input type="text" id="city-name" name="city-name" required>
                <button type="submit" name="add-city" class="add-btn">Add City</button>
            </form>

            <?php if (isset($_GET['edit-id'])): ?>
                <?php
                    $editId = intval($_GET['edit-id']);
                    $cityToEdit = null;
                    foreach ($items as $item) {
                        if ($item['id'] === $editId) {
                            $cityToEdit = $item;
                            break;
                        }
                    }
                ?>
                <?php if ($cityToEdit): ?>
                    <h3>Update City</h3>
                    <form action="" method="post">
                        <input type="hidden" name="city-id" value="<?php echo $cityToEdit['id']; ?>">
                        <label for="city-name">New City Name:</label>
                        <input type="text" id="city-name" name="city-name" value="<?php echo htmlspecialchars($cityToEdit['name']); ?>" required>
                        <button type="submit" name="update-city" class="update-btn">Update City</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>