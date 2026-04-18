<?php
require_once 'lib/jwt_helper.php';
$auth_user  = JWTHelper::authenticate();
$page_title = 'My Profile';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/header.php'; ?>

<body class="bg-gray-50 font-sans" data-page="profile">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 h-14 flex items-center gap-3 shrink-0">
                <button id="sidebar-toggle" class="btn-icon">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title">My Profile</h1>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-2xl mx-auto space-y-6">

                    <!-- Profile card -->
                    <div class="card p-6">
                        <div class="flex items-center gap-5 mb-6">
                            <div id="profile-avatar" class="w-16 h-16 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                                <?= strtoupper(substr($auth_user->first_name ?? 'U', 0, 1) . substr($auth_user->last_name ?? '', 0, 1)) ?>
                            </div>
                            <div>
                                <h2 id="profile-name" class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars(($auth_user->first_name ?? '') . ' ' . ($auth_user->last_name ?? '')) ?>
                                </h2>
                                <p id="profile-designation" class="text-sm text-gray-500"><?= htmlspecialchars($auth_user->designation ?? '—') ?></p>
                                <span class="badge badge-indigo mt-1 capitalize"><?= htmlspecialchars($auth_user->role ?? 'staff') ?></span>
                            </div>
                        </div>

                        <h3 class="section-title mb-4">Personal Information</h3>
                        <form id="profile-form" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">First Name</label>
                                    <input name="first_name" id="pf-first-name" type="text" class="form-input"
                                        value="<?= htmlspecialchars($auth_user->first_name ?? '') ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Last Name</label>
                                    <input name="last_name" id="pf-last-name" type="text" class="form-input"
                                        value="<?= htmlspecialchars($auth_user->last_name ?? '') ?>" required>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input name="email" id="pf-email" type="email" class="form-input"
                                    value="" placeholder="Enter email address">
                            </div>
                            <div>
                                <label class="form-label">Designation</label>
                                <input name="designation" id="pf-designation" type="text" class="form-input"
                                    value="<?= htmlspecialchars($auth_user->designation ?? '') ?>">
                            </div>
                            <div class="flex items-center gap-2 pt-2">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check mr-1.5"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change password -->
                    <div class="card p-6">
                        <h3 class="section-title mb-4">Change Password</h3>
                        <form id="password-form" class="space-y-4">
                            <div>
                                <label class="form-label">Current Password</label>
                                <input name="current_password" id="pf-current-pw" type="password" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">New Password</label>
                                <input name="new_password" id="pf-new-pw" type="password" class="form-input"
                                    placeholder="Min 8 characters" required minlength="8">
                            </div>
                            <div>
                                <label class="form-label">Confirm New Password</label>
                                <input name="confirm_password" id="pf-confirm-pw" type="password" class="form-input" required>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-lock mr-1.5"></i>Update Password
                            </button>
                        </form>
                    </div>

                    <!-- Account info (read-only) -->
                    <div class="card p-6">
                        <h3 class="section-title mb-4">Account Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-500">Staff ID</dt>
                                <dd class="text-sm text-gray-900 font-mono"><?= htmlspecialchars($auth_user->staff_id ?? '—') ?></dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-sm font-medium text-gray-500">Username</dt>
                                <dd class="text-sm text-gray-900"><?= htmlspecialchars($auth_user->username ?? '—') ?></dd>
                            </div>
                            <div class="flex justify-between py-2">
                                <dt class="text-sm font-medium text-gray-500">Account Role</dt>
                                <dd class="text-sm text-gray-900 capitalize"><?= htmlspecialchars($auth_user->role ?? 'staff') ?></dd>
                            </div>
                        </dl>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="/assets/js/pages/profile.js?v=2.0" defer></script>

</body>

</html>