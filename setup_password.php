<?php
// setup_password.php - RUN THIS ONCE, THEN DELETE
require_once 'config.php';

echo "<h2>Password Fix Tool</h2>";

// Generate correct hash
$password = '123456';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "<p>Password: <strong>123456</strong></p>";
echo "<p>Generated Hash: <strong>$hash</strong></p>";

// Update ALL users
$update = "UPDATE users SET password = '$hash'";
if (mysqli_query($conn, $update)) {
    $count = mysqli_affected_rows($conn);
    echo "<p style='color:green;'>✅ Updated $count users!</p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
}

// Show users
$users = mysqli_query($conn, "SELECT id, name, username, role FROM users");
echo "<h3>All Users:</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th>Password OK?</th></tr>";
while ($u = mysqli_fetch_assoc($users)) {
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id={$u['id']}"));
    $verify = password_verify('123456', $check['password']);
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['name']}</td>";
    echo "<td><strong>{$u['username']}</strong></td>";
    echo "<td>{$u['role']}</td>";
    echo "<td style='color:" . ($verify ? 'green' : 'red') . ";'>" . ($verify ? '✅ OK' : '❌ FAIL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Also try to create admin with different passwords for fallback
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE username='admin'");
if (mysqli_num_rows($admin_check) == 0) {
    // Create admin with plain password as fallback
    mysqli_query($conn, "INSERT INTO users (name, username, password, role) VALUES ('Admin', 'admin', '$hash', 'admin')");
    echo "<p style='color:blue;'>Admin user created!</p>";
}

echo "<br><p><strong>Try these logins:</strong></p>";
echo "<ul>";
echo "<li>Username: <strong>admin</strong> | Password: <strong>123456</strong></li>";
echo "<li>Username: <strong>selam</strong> | Password: <strong>123456</strong></li>";
echo "</ul>";

echo "<br><p style='color:red;'><strong>DELETE THIS FILE AFTER USE!</strong></p>";
echo "<p><a href='index.php' style='font-size:20px;'>GO TO LOGIN PAGE</a></p>";
?>