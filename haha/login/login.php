<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

start_secure_session();
login_from_remember_cookie();

if (!empty($_SESSION['login'])) {
    redirect('../daftar_mahasiswa/index.php');
}

$error = '';
$flash = consume_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);

        if (attempt_login($username, $password, $remember)) {
            flash('success', 'Login berhasil. Selamat datang!');
            redirect('../daftar_mahasiswa/index.php');
        }

        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Daftar Mahasiswa CRUD</title>
    <link rel="stylesheet" href="stylesLogin.css?v=2">
</head>
<body>
    <div class="body">
        <div class="login-container1">
            <div class="login-container2">
                <div class="header-wrapped">
                    <div class="header">
                        <h1 id="welcome">Welcome back</h1>
                    </div>
                    <div class="header2">
                        <h3 id="please">Please enter your details.</h3>
                    </div>
                </div>

                <div class="input-wrapped">
                    <form action="" method="post" autocomplete="off">
                        <?= csrf_field() ?>

                        <?php if ($flash): ?>
                            <div class="message message-<?= e($flash['type']) ?>">
                                <p><?= e($flash['message']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="message message-danger">
                                <p><?= e($error) ?></p>
                            </div>
                        <?php endif; ?>

                        <div>
                            <input type="text" name="username" placeholder="username" id="username" size="30.9px" required autofocus>
                        </div>
                        <div>
                            <input type="password" name="password" placeholder="password" id="password" size="30.9px" required>
                        </div>
                        <div class="sign-in">
                            <div>
                                <input type="checkbox" name="remember" id="remember">
                                <label for="remember">Remember me</label>
                            </div>
                            <div>
                                <button type="submit" name="submit">Sign in</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="foot-wrapped">
                    <div class="footer">
                        <p>Don't have an account? <a href="register.php">Sign up for free!</a></p>
                    </div>
                </div>
            </div>

            <!-- DESIGN CRUD SIMULATOR -->
            <div class="design-container1">
                <div class="design-container2">
                    <div class="crud">
                        <h1 id="crud">CRUD</h1>
                    </div>
                    <div class="simulator">
                        <h3 id="simulator">SIMULATOR</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
