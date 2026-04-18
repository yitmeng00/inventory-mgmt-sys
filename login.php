<?php
require_once 'lib/jwt_helper.php';

// Redirect to dashboard if already logged in
$token = $_COOKIE['ims_token'] ?? null;
if ($token && JWTHelper::decode($token)) {
    header('Location: /dashboard.php');
    exit;
}

$page_title = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-slate-800 min-h-screen flex items-center justify-center p-4" data-page="login">

    <div class="w-full max-w-md">

        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl mb-4">
                <i class="fa-solid fa-boxes-stacked text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">InvenTrack</h1>
            <p class="text-slate-400 text-sm mt-1">Inventory Management System</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-1">Welcome back</h2>
            <p class="text-sm text-gray-500 mb-6">Sign in to your account to continue</p>

            <!-- Error alert -->
            <div id="login-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span id="login-error-msg">Invalid credentials.</span>
            </div>

            <form id="login-form" novalidate>
                <div class="mb-4">
                    <label class="form-label" for="username">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <input id="username" name="username" type="text" required autocomplete="username"
                            class="form-input pl-9" placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label" for="password">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="form-input pl-9 pr-10" placeholder="Enter your password">
                        <button type="button" id="toggle-password"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <button id="login-btn" type="submit"
                    class="btn-primary w-full flex items-center justify-center gap-2 py-2.5">
                    <span id="login-btn-text">Sign In</span>
                    <i id="login-spinner" class="fa-solid fa-circle-notch fa-spin hidden!"></i>
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; <?= date('Y') ?> InvenTrack. All rights reserved.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Toggle password visibility
            document.getElementById('toggle-password').addEventListener('click', function() {
                const pwd = document.getElementById('password');
                const icon = this.querySelector('i');
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    pwd.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });

            document.getElementById('login-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const btn = document.getElementById('login-btn');
                const btnText = document.getElementById('login-btn-text');
                const spinner = document.getElementById('login-spinner');
                const errBox = document.getElementById('login-error');
                const errMsg = document.getElementById('login-error-msg');

                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;

                if (!username || !password) {
                    errMsg.textContent = 'Username and password are required.';
                    errBox.style.display = 'block';
                    return;
                }

                btn.disabled = true;
                btnText.textContent = 'Signing in…';
                spinner.classList.remove('hidden!');
                errBox.style.display = 'none';

                try {
                    const body = new FormData();
                    body.append('username', username);
                    body.append('password', password);

                    const res = await fetch('/db/login_db.php', {
                        method: 'POST',
                        body,
                        credentials: 'same-origin'
                    });
                    const data = await res.json();

                    if (data.success) {
                        window.location.href = '/dashboard.php';
                    } else {
                        errMsg.textContent = data.error_msg || 'Login failed.';
                        errBox.style.display = 'block';
                    }
                } catch (err) {
                    errMsg.textContent = 'Network error. Please try again.';
                    errBox.style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btnText.textContent = 'Sign In';
                    spinner.classList.add('hidden!');
                }
            });
        });
    </script>
</body>

</html>