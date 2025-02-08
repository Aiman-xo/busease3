<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: ./");
    exit();
}

require_once("../db.php");

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check credentials
    $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $row['admin_id'];
            header("Location: ./"); // Redirect to dashboard or replace with inline dashboard logic
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0">
    <div class="flex h-screen">
        <!-- Image Section -->
        <div class="flex items-center justify-center w-7/12 bg-[#C4D8E2]">
            <img class="w-52 h-52" src="../assets/img/admin-with-cogwheels.png" alt="Admin">
        </div>

        <!-- Login Section -->
        <div class="flex flex-col items-center justify-center w-5/12 bg-white">
            <!-- Heading -->
            <div class="relative top-28 text-center">
                <h2 class="text-4xl font-bold">BusEase</h2>
                <p class="mt-2 text-sm text-gray-500">Admin Login</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="" class="relative top-32 w-full flex flex-col items-center">
                <!-- Email Input -->
                <div class="w-80 mb-3">
                    <input class="w-full h-12 px-4 text-base bg-gray-100 rounded-t-lg outline-none" 
                           type="text" name="email" placeholder="Email or Username" required>
                </div>
                <!-- Password Input -->
                <div class="w-80 mb-3">
                    <input class="w-full h-12 px-4 text-base bg-gray-100 rounded-b-lg outline-none" 
                           type="password" name="password" placeholder="Password" required>
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="text-red-500 text-center mb-3"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Login Button -->
                <div class="w-80">
                    <button class="w-full h-14 bg-black text-white text-lg rounded-lg hover:bg-gray-900 transition" 
                            type="submit">
                        Log In
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
