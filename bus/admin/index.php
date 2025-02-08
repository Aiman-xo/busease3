<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once("../db.php");

$message = '';
$messageClass = ''; // Initialize the class for the message styling

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staffName'])) {
    $name = $_POST['staffName'];
    $email = $_POST['email'];
    $contactNo = $_POST['contactNo'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Check for duplicate email before insert or update
    $emailCheckSql = "SELECT staff_id FROM staff WHERE email = '$email'";
    $emailCheckResult = $conn->query($emailCheckSql);
    
    if ($emailCheckResult->num_rows > 0) {
        // Email already exists, check if it's the same record
        $existingStaff = $emailCheckResult->fetch_assoc();
        if (isset($_POST['updateId']) && !empty($_POST['updateId']) && $existingStaff['staff_id'] == $_POST['updateId']) {
            // Allow updating the same record (same email)
            $isDuplicateEmail = false;
        } else {
            // Different staff member is using the same email
            $isDuplicateEmail = true;
        }
    } else {
        // No existing email, safe to proceed
        $isDuplicateEmail = false;
    }
    
    if ($isDuplicateEmail) {
        $message = "Error: Email already exists!";
        $messageClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Error styling
    } else {
        
        if (isset($_POST['updateId']) && !empty($_POST['updateId'])) {
            $updateId = $_POST['updateId'];
            $sql = "UPDATE staff SET name='$name', email='$email', contact_number='$contactNo'" . 
                   ($password ? ", password='$password'" : "") . " WHERE staff_id=$updateId";
        } else {
            $sql = "INSERT INTO staff (name, email, contact_number, password) VALUES ('$name', '$email', '$contactNo', '$password')";
        }
        
        // Execute query once and store the result
        $queryResult = $conn->query($sql);
        
        // Check if the query was successful and set appropriate message and class
        if ($queryResult) {
            $message = !empty($_POST['updateId']) ? "Staff updated successfully!" : "Registration successful!";
            $messageClass = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'; // Success styling
        } else {
            $message = "Error: " . $conn->error;
            $messageClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Error styling
        }

    }

}

// Handle admin password update
if (isset($_POST['updateAdminPassword'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    if ($newPassword !== $confirmNewPassword) {
        $message = "Error: New passwords do not match!";
        $messageClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Error styling
    } else {
        // Fetch current admin password from the database
        $adminId = $_SESSION['admin_id'];
        $sql = "SELECT password FROM admins WHERE admin_id = $adminId";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($currentPassword, $admin['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateSql = "UPDATE admins SET password = '$hashedPassword' WHERE admin_id = $adminId";
                $update_status = $conn->query($updateSql);

                $message = $update_status ? "Password updated successfully!" : "Error: " . $conn->error;
                $messageClass = $update_status ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4' : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Success/Error styling
            } else {
                $message = "Error: Current password is incorrect!";
                $messageClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Error styling
            }
        } else {
            $message = "Error: Admin not found!";
            $messageClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'; // Error styling
        }
    }
}

// Handle delete request
if (isset($_GET['deleteId'])) {
    $deleteId = $_GET['deleteId'];
    $deleteSql = "DELETE FROM staff WHERE staff_id=$deleteId";
    $conn->query($deleteSql);
    header("Location: ./");
    exit();
}

// Fetch staff list
$sql = "SELECT staff_id, name, email, contact_number FROM staff";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-semibold">Admin Dashboard</h1>

        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>

    </header>

    <main class="container mx-auto p-4">
        <?php if (isset($message)) echo "<div class='$messageClass'>$message</div>"; ?>

        <div class="flex space-x-8 mb-8">
            <!-- Register or Update Staff Section -->
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <h2 class="text-lg font-bold mb-4">Register or Update Staff</h2>
                <form action="/bus/admin/" method="post" class="space-y-4">
                    <input type="hidden" id="updateId" name="updateId">
                    <div>
                        <label for="staffName" class="block text-sm font-medium text-gray-700">Staff Name</label>
                        <input type="text" id="staffName" name="staffName" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="contactNo" class="block text-sm font-medium text-gray-700">Contact No</label>
                        <input type="tel" id="contactNo" name="contactNo" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
                    </div>
                </form>
            </div>
            
            <!-- Update Admin Password Section -->
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <h2 class="text-lg font-bold mb-4">Update Admin Password</h2>
                <form  action="/bus/admin/" method="post" class="space-y-4">
                    <input type="hidden" name="updateAdminPassword" value="1">
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input type="password" id="currentPassword" name="currentPassword" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label for="confirmNewPassword" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" id="confirmNewPassword" name="confirmNewPassword" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Update Password</button>
                    </div>
                </form>
            </div>            
        </div>


        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-4">Staff List</h2>
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Name</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Contact</th>
                        <th class="px-4 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="text-sm even:bg-gray-50">
                                <td class="px-4 py-2 border"><?php echo $row['staff_id']; ?></td>
                                <td class="px-4 py-2 border"><?php echo $row['name']; ?></td>
                                <td class="px-4 py-2 border"><?php echo $row['email']; ?></td>
                                <td class="px-4 py-2 border"><?php echo $row['contact_number']; ?></td>
                                <td class="px-4 py-2 border flex space-x-2">
                                    <button onclick="editStaff(<?php echo $row['staff_id']; ?>, '<?php echo $row['name']; ?>', '<?php echo $row['email']; ?>', '<?php echo $row['contact_number']; ?>')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">Edit</button>
                                    <a href="?deleteId=<?php echo $row['staff_id']; ?>" onclick="return confirm('Are you sure you want to delete this staff?');" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-2 border text-center">No staff found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function editStaff(id, name, email, contactNo) {
            document.getElementById('updateId').value = id;
            document.getElementById('staffName').value = name;
            document.getElementById('email').value = email;
            document.getElementById('contactNo').value = contactNo;
            document.getElementById('password').required = false;
            document.getElementById('confirmPassword').required = false;
        }
    </script>
</body>
</html>
