<?php
require __DIR__ . '/vendor/autoload.php';
$users = App\Models\User::where('role', 'hr')->get();
echo 'HR count: ' . count($users) . "\n";
foreach ($users as $u) {
    echo $u->id . ' ' . $u->email . ' ' . $u->role . ' ' . ($u->is_active ? 'active' : 'inactive') . "\n";
}
