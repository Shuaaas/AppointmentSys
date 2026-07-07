<?php
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$users = App\Models\User::all();
foreach ($users as $user) {
    echo sprintf("%s %s role=%s active=%s requested_role=%s\n", $user->id, $user->email, $user->role, $user->is_active ? 'yes' : 'no', $user->requested_role);
}
