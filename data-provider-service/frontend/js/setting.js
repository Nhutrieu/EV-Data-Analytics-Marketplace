// frontend/js/settings.js

// URL API backend cho settings (data provider service)
const SETTINGS_API_URL =
    "/index.php?page=settings_api";

//
// -------- API wrappers --------
//

async function apiGetSettings() {
    const res = await fetch(SETTINGS_API_URL, {
        method: "GET",
        headers: {
            Accept: "application/json",
        },
        credentials: "same-origin",
    });

    if (!res.ok) {
        let msg = "Không tải được cài đặt tài khoản từ server.";
        try {
            const body = await res.json();
            if (body && body.message) msg = body.message;
        } catch (_) { }
        throw new Error(msg);
    }

    return res.json();
}

async function apiUpdateCompany(payload) {
    const url = SETTINGS_API_URL + "&section=company";
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
    });

    let data = {};
    try {
        data = await res.json();
    } catch (_) { }

    if (!res.ok || data.success === false) {
        const msg = data.message || "Không lưu được thông tin công ty.";
        throw new Error(msg);
    }

    return data;
}

async function apiChangePassword(payload) {
    const url = SETTINGS_API_URL + "&section=password";
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
    });

    let data = {};
    try {
        data = await res.json();
    } catch (_) { }

    if (!res.ok || data.success === false) {
        const msg = data.message || "Không đổi được mật khẩu.";
        throw new Error(msg);
    }

    return data;
}

async function apiUpdateSystemSettings(payload) {
    const url = SETTINGS_API_URL + "&section=system";
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
    });

    let data = {};
    try {
        data = await res.json();
    } catch (_) { }

    if (!res.ok || data.success === false) {
        const msg = data.message || "Không lưu được cài đặt hệ thống.";
        throw new Error(msg);
    }

    return data;
}

//
// -------- UI: load data --------
//

async function loadSettingsPage() {
    try {
        const data = await apiGetSettings();
        const company = data.company || {};
        const login = data.login || {};
        const system = data.system || {};

        // --- Thông tin công ty ---
        const companyNameInput = document.getElementById("company-name");
        const companyEmailInput = document.getElementById("company-email");
        const companyPhoneInput = document.getElementById("company-phone");
        const contactPersonInput = document.getElementById("contact-person");
        const addressInput = document.getElementById("company-address");
        const descriptionInput = document.getElementById("company-description");

        if (companyNameInput) companyNameInput.value = company.company_name || "";
        if (companyEmailInput) companyEmailInput.value = company.contact_email || "";
        if (companyPhoneInput) companyPhoneInput.value = company.contact_phone || "";
        if (contactPersonInput)
            contactPersonInput.value = company.contact_person || "";
        if (addressInput) addressInput.value = company.address || "";
        if (descriptionInput) descriptionInput.value = company.description || "";

        // --- Thông tin đăng nhập (email) ---
        const loginEmailInput = document.querySelector(
            "#login-form input[type='email']"
        );
        if (loginEmailInput && login.login_email) {
            loginEmailInput.value = login.login_email;
        }

        // --- Cài đặt hệ thống ---
        const timezoneSelect = document.getElementById("timezone");
        const dateFormatSelect = document.getElementById("date-format");
        const currencySelect = document.getElementById("currency");

        if (timezoneSelect && system.timezone) {
            timezoneSelect.value = system.timezone;
        }
        if (dateFormatSelect && system.date_format) {
            dateFormatSelect.value = system.date_format;
        }
        if (currencySelect && system.currency) {
            currencySelect.value = system.currency;
        }

        // Ngôn ngữ: chỉ là UI, không lấy từ backend nữa.
        // Nếu muốn, có thể đọc từ localStorage:
        const languageSelect = document.getElementById("language");
        if (languageSelect) {
            const savedLang = localStorage.getItem("provider_language");
            if (savedLang) {
                languageSelect.value = savedLang;
            }
            languageSelect.addEventListener("change", () => {
                localStorage.setItem("provider_language", languageSelect.value);
            });
        }
    } catch (err) {
        console.error(err);
        alert("Không tải được cài đặt tài khoản từ server.");
    }
}

//
// -------- UI: form handlers --------
//

function initCompanyForm() {
    const form = document.getElementById("company-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const companyNameInput = document.getElementById("company-name");
        const companyEmailInput = document.getElementById("company-email");
        const companyPhoneInput = document.getElementById("company-phone");
        const contactPersonInput = document.getElementById("contact-person");
        const addressInput = document.getElementById("company-address");
        const descriptionInput = document.getElementById("company-description");

        const payload = {
            company_name: companyNameInput ? companyNameInput.value.trim() : "",
            contact_email: companyEmailInput ? companyEmailInput.value.trim() : "",
            contact_phone: companyPhoneInput ? companyPhoneInput.value.trim() : "",
            contact_person: contactPersonInput
                ? contactPersonInput.value.trim()
                : "",
            address: addressInput ? addressInput.value.trim() : "",
            description: descriptionInput ? descriptionInput.value.trim() : "",
        };

        try {
            const result = await apiUpdateCompany(payload);
            alert(result.message || "Đã cập nhật thông tin công ty.");
        } catch (err) {
            console.error(err);
            alert(err.message || "Không lưu được thông tin công ty.");
        }
    });
}

function initLoginForm() {
    const form = document.getElementById("login-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const newPasswordInput = document.getElementById("new-password");
        const confirmPasswordInput = document.getElementById("confirm-password");

        const payload = {
            new_password: newPasswordInput ? newPasswordInput.value : "",
            confirm_password: confirmPasswordInput
                ? confirmPasswordInput.value
                : "",
        };

        try {
            const result = await apiChangePassword(payload);
            alert(result.message || "Đã đổi mật khẩu thành công.");

            if (newPasswordInput) newPasswordInput.value = "";
            if (confirmPasswordInput) confirmPasswordInput.value = "";
        } catch (err) {
            console.error(err);
            alert(err.message || "Không đổi được mật khẩu.");
        }
    });
}

function initSystemSettingsForm() {
    const form = document.getElementById("system-settings-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const timezoneSelect = document.getElementById("timezone");
        const dateFormatSelect = document.getElementById("date-format");
        const currencySelect = document.getElementById("currency");

        const payload = {
            // language: bỏ không gửi lên backend nữa
            timezone: timezoneSelect ? timezoneSelect.value : "+7",
            date_format: dateFormatSelect ? dateFormatSelect.value : "dd/mm/yyyy",
            currency: currencySelect ? currencySelect.value : "VND",
        };

        try {
            const result = await apiUpdateSystemSettings(payload);
            alert(result.message || "Đã lưu cài đặt hệ thống.");
        } catch (err) {
            console.error(err);
            alert(err.message || "Không lưu được cài đặt hệ thống.");
        }
    });
}

//
// -------- init --------
//

document.addEventListener("DOMContentLoaded", () => {
    loadSettingsPage();
    initCompanyForm();
    initLoginForm();
    initSystemSettingsForm();
});
