document.addEventListener("DOMContentLoaded", async () => {
    await loadProfileEmail();
    bindEvents();
});

async function loadProfileEmail() {
    const data = await API.get("/db/user_db.php?action=me");
    if (data.success && data.user) {
        document.getElementById("pf-email").value = data.user.email || "";
    }
}

function bindEvents() {
    document
        .getElementById("profile-form")
        .addEventListener("submit", handleProfileSave);
    document
        .getElementById("password-form")
        .addEventListener("submit", handlePasswordChange);

    const fnInput = document.getElementById("pf-first-name");
    const lnInput = document.getElementById("pf-last-name");
    const desgInput = document.getElementById("pf-designation");
    [fnInput, lnInput, desgInput].forEach((el) =>
        el.addEventListener("input", updateAvatarPreview),
    );
}

function updateAvatarPreview() {
    const first = document.getElementById("pf-first-name").value.trim();
    const last = document.getElementById("pf-last-name").value.trim();
    const initials = (first[0] ?? "") + (last[0] ?? "");
    document.getElementById("profile-avatar").textContent =
        initials.toUpperCase();
    document.getElementById("profile-name").textContent =
        [first, last].filter(Boolean).join(" ") || "—";
    document.getElementById("profile-designation").textContent =
        document.getElementById("pf-designation").value.trim() || "—";
}

async function handleProfileSave(e) {
    e.preventDefault();
    const btn = e.submitter;
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Saving…';

    try {
        const form = document.getElementById("profile-form");
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        const data = await API.post(
            "/db/user_db.php?action=update_profile",
            payload,
        );
        if (data.success) {
            Toast.success("Saved", "Profile updated successfully");
        } else {
            Toast.error("Error", data.error_msg || "Failed to update profile");
        }
    } catch (err) {
        Toast.error("Error", err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

async function handlePasswordChange(e) {
    e.preventDefault();
    const newPw = document.getElementById("pf-new-pw").value;
    const confirmPw = document.getElementById("pf-confirm-pw").value;

    if (newPw !== confirmPw) {
        Toast.warning("Mismatch", "New password and confirmation do not match");
        return;
    }
    if (newPw.length < 8) {
        Toast.warning("Too Short", "Password must be at least 8 characters");
        return;
    }

    const btn = e.submitter;
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
        '<i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>Updating…';

    try {
        const payload = {
            current_password: document.getElementById("pf-current-pw").value,
            new_password: newPw,
        };
        const data = await API.post(
            "/db/user_db.php?action=change_password",
            payload,
        );
        if (data.success) {
            Toast.success("Updated", "Password changed successfully");
            document.getElementById("password-form").reset();
        } else {
            Toast.error("Error", data.error_msg || "Failed to change password");
        }
    } catch (err) {
        Toast.error("Error", err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}
