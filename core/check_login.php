<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=omnimart', 'root', '');
$stmt = $pdo->query('SELECT id, email, password FROM admins');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $check = password_verify('admin@123', $row['password']) ? 'MATCH' : 'NO';
    echo "{$row['email']} => $check\n";
}
echo "\nAll admins:\n";
$stmt2 = $pdo->query('SELECT id, email, role_id FROM admins');
foreach ($stmt2 as $row) {
    echo "ID:{$row['id']} Email:{$row['email']} Role:{$row['role_id']}\n";
}
