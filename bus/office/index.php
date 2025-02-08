<?php
session_start();
$location = "/bus/office/";
// Check if the teacher is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

require_once("../db.php");

// Initialize message variables
$message = '';
$messageClass = '';

// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Update teacher password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_teacher_password'])) {
    $new_password= !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_BCRYPT) : null;
    $teacher_id = $_SESSION['staff_id']; 
    $stmt = $conn->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
    $stmt->bind_param("si", $new_password, $teacher_id);
    if ($stmt->execute()) {
        $message = 'Password updated successfully!';
        $messageClass = 'bg-green-500 text-white p-4 rounded-lg';
    } else {
        $message = 'Failed to update password.';
        $messageClass = 'bg-red-500 text-white p-4 rounded-lg';
    }
    $stmt->close();
}

// Add or edit student functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_edit_student'])) {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];
    $student_phone = $_POST['student_phone'];
    $student_department = $_POST['student_department'];
    $student_place = $_POST['student_place'];
    $student_bus_fare = $_POST['student_bus_fare'];
    $student_payment_status = $_POST['student_payment_status'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    $email_check_query = "SELECT * FROM students WHERE " . ($id ? "id != $id AND " : "") . "(email = ? OR student_id = ?)";

    $stmt = $conn->prepare($email_check_query);

    $stmt->bind_param("si", $student_email, $student_id); // Use student_id if updating

    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows > 0) {
        $_SESSION['message'] = 'This email or Student ID is already taken. Please use another one.';
        $_SESSION['messageClass'] = 'bg-red-500 text-white p-4 rounded-lg';
        $stmt->close();
        header("Location: ".$location);
        exit();
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE students SET student_id = ?, student_name = ?, email = ?, phone = ?, department = ?, place = ?, bus_fare = ?, payment_status = ? WHERE id = ?");
        $stmt->bind_param("isssssssi", $student_id, $student_name, $student_email, $student_phone, $student_department, $student_place, $student_bus_fare, $student_payment_status, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Student updated successfully!';
            $_SESSION['messageClass'] = 'bg-green-500 text-white p-4 rounded-lg';
        } else {
            $_SESSION['message'] = 'Failed to update student.';
            $_SESSION['messageClass'] = 'bg-red-500 text-white p-4 rounded-lg';
        }
    } else {

        $stmt = $conn->prepare("INSERT INTO students (student_id, student_name, email, phone, place, department,  bus_fare, payment_status, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $student_id, $student_name, $student_email, $student_phone, $student_place, $student_department,  $student_bus_fare, $student_payment_status, $password);
      
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Student added successfully!';
            $_SESSION['messageClass'] = 'bg-green-500 text-white p-4 rounded-lg';
        } else {
            $_SESSION['message'] = 'Failed to add student.';
            $_SESSION['messageClass'] = 'bg-red-500 text-white p-4 rounded-lg';
        }
    }
    $stmt->close();

    // Redirect to the same page without the edit_id in the URL
    header("Location: " .$location);
    exit(); // Make sure to exit after redirect to prevent further code execution
}


// Delete student
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
         $_SESSION['message']= 'Student deleted successfully!';
         $_SESSION['messageClass']  = 'bg-green-500 text-white p-4 rounded-lg';
    } else {
         $_SESSION['message'] = 'Failed to delete student.';
         $_SESSION['messageClass'] = 'bg-red-500 text-white p-4 rounded-lg';
    }
    $stmt->close();
    // Redirect to the same page without the edit_id in the URL
    header("Location: " . $location);
    exit(); // Make sure to exit after redirect to prevent further code execution    
}

// Fetch students
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto p-8">
        <!-- Dashboard Heading -->
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Teacher Dashboard</h1>

        <!-- Display success or error message after redirection -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="my-2 <?= $_SESSION['messageClass'] ?>">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['messageClass']); // Clear the message after displaying it ?>
        <?php endif; ?>

        <!-- Teacher Password Update Section -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800">Update Teacher Password</h2>
            <form method="POST" class="mt-4">
                <input type="password" name="new_password" placeholder="New Password" class="p-2 border border-gray-300 rounded-md mb-4" required>
                <button type="submit" name="update_teacher_password" class="bg-blue-500 text-white p-2 rounded-md">Update Password</button>
            </form>

            <!-- Logout Section -->
            <form method="POST" action="">
                <button type="submit" name="logout" class="bg-red-600 text-white font-semibold py-2 px-6 rounded-md hover:bg-red-700 transition duration-300">
                    Logout
                </button>
            </form>
        </div>

        <!-- Add/Edit Student Section -->
        <div class="mb-8 p-6 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center"><?= isset($_GET['edit_id']) ? 'Edit Student' : 'Add Student' ?></h2>

            <?php
            $student = null;
            if (isset($_GET['edit_id'])) {
                // Fetch student data for editing
                $edit_id = $_GET['edit_id'];
                $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
                $stmt->bind_param("i", $edit_id);
                $stmt->execute();
                $student = $stmt->get_result()->fetch_assoc();
            }
            ?>

            <form method="POST" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($student): ?>
                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                <?php endif; ?>

                <!-- Student ID -->
                <div>
                    <label for="student_id" class="block text-gray-600 font-medium text-sm">Student ID</label>
                    <input type="text" name="student_id" value="<?= $student['student_id'] ?? '' ?>" placeholder="Student ID" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Name -->
                <div>
                    <label for="student_name" class="block text-gray-600 font-medium text-sm">Student Name</label>
                    <input type="text" name="student_name" value="<?= $student['student_name'] ?? '' ?>" placeholder="Student Name" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Email -->
                <div>
                    <label for="student_email" class="block text-gray-600 font-medium text-sm">Student Email</label>
                    <input type="email" name="student_email" value="<?= $student['email'] ?? '' ?>" placeholder="Student Email" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Phone -->
                <div>
                    <label for="student_phone" class="block text-gray-600 font-medium text-sm">Student Phone</label>
                    <input type="text" name="student_phone" value="<?= $student['phone'] ?? '' ?>" placeholder="Student Phone" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Department -->
                <div>
                    <label for="student_department" class="block text-gray-600 font-medium text-sm">Department</label>
                    <input type="text" name="student_department" value="<?= $student['department'] ?? '' ?>" placeholder="Department" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Place -->
                <div>
                    <label for="student_place" class="block text-gray-600 font-medium text-sm">Place</label>
                    <input type="text" name="student_place" value="<?= $student['place'] ?? '' ?>" placeholder="Place" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Student Bus Fare -->
                <div>
                    <label for="student_bus_fare" class="block text-gray-600 font-medium text-sm">Bus Fare</label>
                    <input type="number" name="student_bus_fare" value="<?= $student['bus_fare'] ?? '' ?>" placeholder="Bus Fare" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Payment Status -->
                <div>
                    <label for="student_payment_status" class="block text-gray-600 font-medium text-sm">Payment Status</label>
                    <select name="student_payment_status" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        <option value="Paid" <?= isset($student) && $student['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="Pending" <?= isset($student) && $student['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                <?php if (!isset($_GET['edit_id'])): ?>
                <div>
                    <label for="password" class="block text-gray-600 font-medium text-sm">Temp Password</label>
                    <input type="password" name="password" placeholder="Temp Password" class="p-2 border border-gray-300 rounded-md" required>
                </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <div class="col-span-3 flex justify-center">
                    <button type="submit" name="add_edit_student" class="bg-<?= isset($student) ? 'yellow' : 'green' ?>-500 text-white px-4 py-2 rounded-md hover:bg-<?= isset($student) ? 'yellow' : 'green' ?>-600 focus:outline-none focus:ring-2 focus:ring-<?= isset($student) ? 'yellow' : 'green' ?>-500">
                        <?= isset($student) ? 'Update Student' : 'Add Student' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Student List with Edit and Delete Options -->
        <h2 class="text-xl font-semibold text-gray-800">Student List</h2>
        <table class="table-auto w-full border-collapse mt-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 text-left">Student ID</th>
                    <th class="p-2 text-left">Student Name</th>
                    <th class="p-2 text-left">Phone</th>
                    <th class="p-2 text-left">Place</th>
                    <th class="p-2 text-left">Department</th>
                    <th class="p-2 text-left">Payment Status</th>
                    <th class="p-2 text-left">Amount</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="p-2"><?= $row['student_id'] ?></td>
                        <td class="p-2"><?= $row['student_name'] ?></td>
                        <td class="p-2"><?= $row['phone'] ?></td>
                        <td class="p-2"><?= $row['place'] ?></td>
                        <td class="p-2"><?= $row['department'] ?></td>
                        <td class="p-2"><?= $row['payment_status'] ?></td>
                        <td class="p-2"><?= $row['bus_fare'] ?></td>
                        <td class="p-2">
                            <a href="?edit_id=<?= $row['id'] ?>" class="bg-yellow-500 text-white p-2 rounded-md">Edit</a>
                            <a href="?delete_id=<?= $row['id'] ?>" class="bg-red-500 text-white p-2 rounded-md" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
