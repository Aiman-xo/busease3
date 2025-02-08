<?php
session_start(); // Start the session to store user information after login

if (isset($_SESSION['staff_id'])) {
    header("Location: ./");
    exit();
}

require_once("../db.php");

// Handle login request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];  // Get email from form
    $password = $_POST['password'];  // Get password from form

    // SQL query to check if the email exists and the password matches
    $sql = "SELECT * FROM staff WHERE email = '$email'";
    $result = $conn->query($sql);

    // Check if the query returns a row (i.e., the user is found)
    if ($result->num_rows > 0) {
        // User found, set session variables and redirect to the student details page
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['staff_id'] = $row['staff_id']; 
            header("Location: ./"); // Redirect to student details page
            exit();
        }
    } else {
        // If the credentials don't match, show an error message
        $error_message = "Invalid email or password!";
    }
}

$conn->close();  // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Teacher Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 bg-gray-100">
    <div class="flex h-screen">
        <!-- Left image section -->
        <div class="flex items-center justify-center bg-[#C4D8E2] w-1/2">
            <img class="w-48 h-48" src="../assets/img/teacher1.png" alt="Student Image">
        </div>

        <!-- Login form section -->
        <div class="w-1/2 bg-white flex flex-col items-center justify-center p-6">
            <h2 class="text-4xl font-semibold mb-4">BusEase</h2>
            <p class="text-sm text-[#858383] mb-6">Teacher Login</p>

            <!-- Display error message if login fails -->
            <?php if (isset($error_message)) { ?>
                <div class="bg-red-500 text-white text-center p-2 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>

            <!-- Email Input -->
            <form method="POST" action="login.php" class="space-y-4">
                <div>
                    <input class="w-80 h-12 px-4 border border-gray-300 bg-[#f1f1f1] rounded-md mb-4 text-base" type="text" name="email" placeholder="Email or Username" required>
                </div>

                <!-- Password Input -->
                <div>
                    <input class="w-80 h-12 px-4 border border-gray-300 bg-[#f1f1f1] rounded-md mb-6 text-base" type="password" name="password" placeholder="Password" required>
                </div>

                <!-- Login Button -->
                <div>
                    <button type="submit" class="w-80 h-12 bg-[#070707d1] text-white rounded-md text-base">Log In</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
