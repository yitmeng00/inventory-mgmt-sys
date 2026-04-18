/**
 * HTTP client — cookies are sent automatically (same-origin).
 * All methods return parsed JSON or throw on network/HTTP error.
 */
const API = (() => {
    async function request(url, options = {}) {
        const res = await fetch(url, {
            credentials: "same-origin",
            ...options,
        });
        const data = await res.json().catch(() => ({
            success: false,
            error_msg: "Invalid server response",
        }));
        if (res.status === 401) {
            window.location.href = "/login.php";
            throw new Error("Unauthenticated");
        }
        return data;
    }

    return {
        get: (url) => request(url),
        post: (url, body) =>
            request(url, {
                method: "POST",
                body,
                headers:
                    body instanceof FormData
                        ? undefined
                        : { "Content-Type": "application/json" },
            }),
        put: (url, payload) =>
            request(url, {
                method: "PUT",
                body: JSON.stringify(payload),
                headers: { "Content-Type": "application/json" },
            }),
        delete: (url, payload) =>
            request(url, {
                method: "DELETE",
                body: JSON.stringify(payload),
                headers: { "Content-Type": "application/json" },
            }),
        postForm: (url, form) =>
            request(url, {
                method: "POST",
                body: form instanceof FormData ? form : new FormData(form),
            }),
    };
})();
