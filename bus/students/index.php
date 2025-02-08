<?php
// Start the session
session_start();
$location = "/bus/students/";

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once("../db.php");

// Fetch student data from the database
$student_id = $_SESSION['student_id']; 
$sql = "SELECT `id`, `student_id`, `student_name`, `phone`, `place`, `department`, `bus_fare`, `payment_status`, `email` FROM `students` WHERE `student_id` = $student_id";
$result = $conn->query($sql);
$student = $result->fetch_assoc();


// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Handle Password Update
if (isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password

    // Update password in the database
    $update_sql = "UPDATE `students` SET `password` = '$hashed_password' WHERE `student_id` = $student_id";
    if ($conn->query($update_sql)) {
        $success_message = "Password updated successfully!";
    } else {
        $error_message = "Failed to update password!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
        <!-- Logout Section -->
        <form method="POST" action="<?= $location ?>" class="mb-4 flex justify-end">
            <button type="submit" name="logout" class="bg-red-600 text-white font-semibold py-2 px-6 rounded-md hover:bg-red-700 transition duration-300">
                Logout
            </button>
        </form>

        <!-- Student Details Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Student Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-field">
                    <label class="block text-gray-600">Student ID:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['student_id']; ?></p>
                </div>
                <div class="input-field">
                    <label class="block text-gray-600">Student Name:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['student_name']; ?></p>
                </div>
                <div class="input-field">
                    <label class="block text-gray-600">Place:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['place']; ?></p>
                </div>
                <div class="input-field">
                    <label class="block text-gray-600">Department:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['department']; ?></p>
                </div>
                <div class="input-field">
                    <label class="block text-gray-600">Email:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['email']; ?></p>
                </div>
            </div>
        </div>

        <!-- Payment Details Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Payment Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-field">
                    <label class="block text-gray-600">Payment Status:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md"><?php echo $student['payment_status']; ?></p>
                </div>
                <div class="input-field">
                    <label class="block text-gray-600">Amount:</label>
                    <p class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-md">₹<?php echo $student['bus_fare']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pay Button (If Payment is Pending) -->
        <?php if ($student['payment_status'] == 'pending'): ?>
            <div class="flex justify-center">
                <button class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-md hover:bg-blue-700 transition duration-300">
                    Pay
                </button>
            </div>
        <?php endif; ?>

        <!-- Password Update Section -->
        <div class="mt-10">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Update Password</h2>
            <?php if (isset($success_message)): ?>
                <div class="mb-4 text-green-600"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="mb-4 text-red-600"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="" class="space-y-4">
                <div class="input-field">
                    <label class="block text-gray-600">New Password:</label>
                    <input type="password" name="new_password" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="update_password" class="bg-green-600 text-white font-semibold py-2 px-6 rounded-md hover:bg-green-700 transition duration-300">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
