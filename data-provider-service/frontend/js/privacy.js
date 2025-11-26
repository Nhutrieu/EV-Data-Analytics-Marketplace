// frontend/js/privacy.js

// URL API backend cho privacy (data provider service)
const PRIVACY_API_URL =
    "/index.php?page=privacy_api";

//
// -------- API wrappers --------
//

async function apiGetPrivacySettings() {
    const res = await fetch(PRIVACY_API_URL, {
        method: "GET",
        headers: {
            Accept: "application/json",
        },
        credentials: "same-origin", // để gửi cookie session (provider_id)
    });

    if (!res.ok) {
        let errMsg = "Không tải được cài đặt bảo mật từ server.";
        try {
            const errBody = await res.json();
            if (errBody && errBody.message) {
                errMsg = errBody.message;
            }
        } catch (_) {
            // ignore parse error
        }
        throw new Error(errMsg);
    }

    return res.json();
}

async function apiSavePrivacySettings(payload) {
    const res = await fetch(PRIVACY_API_URL, {
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
    } catch (_) {
        // nếu body không phải JSON, cứ để trống
    }

    if (!res.ok || data.success === false) {
        const msg = data.message || "Không lưu được cài đặt bảo mật.";
        throw new Error(msg);
    }

    return data;
}

//
// -------- UI logic --------
//

async function loadPrivacySettings() {
    try {
        const s = await apiGetPrivacySettings();

        const anonymizeCheckbox = document.getElementById("anonymize-data");
        const standardSelect = document.getElementById("privacy-standard");
        const retentionInput = document.getElementById("retention-months");
        const accessControlSelect = document.getElementById("access-control");

        if (anonymizeCheckbox) {
            // s.anonymize từ PHP là bool, nhưng phòng trường hợp là "0"/"1"
            const value =
                typeof s.anonymize === "boolean"
                    ? s.anonymize
                    : !!parseInt(s.anonymize ?? 1, 10);
            anonymizeCheckbox.checked = value;
        }

        if (standardSelect && s.standard) {
            standardSelect.value = s.standard;
        }

        if (retentionInput && s.retention_months != null) {
            retentionInput.value = s.retention_months;
        }

        if (accessControlSelect && s.access_control) {
            accessControlSelect.value = s.access_control;
        }
    } catch (err) {
        console.error(err);
        alert("Không tải được cài đặt bảo mật từ server.");
    }
}

function initPrivacyForm() {
    const form = document.getElementById("privacy-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const anonymizeCheckbox = document.getElementById("anonymize-data");
        const standardSelect = document.getElementById("privacy-standard");
        const retentionInput = document.getElementById("retention-months");
        const accessControlSelect = document.getElementById("access-control");

        const payload = {
            anonymize: anonymizeCheckbox ? anonymizeCheckbox.checked : false,
            standard: standardSelect ? standardSelect.value : "GDPR",
            retention_months: retentionInput
                ? Number(retentionInput.value || 0)
                : 0,
            access_control: accessControlSelect
                ? accessControlSelect.value
                : "verified-buyers",
        };

        try {
            const result = await apiSavePrivacySettings(payload);
            alert(result.message || "Đã lưu cài đặt bảo mật.");
        } catch (err) {
            console.error(err);
            alert(err.message || "Không lưu được cài đặt bảo mật.");
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    loadPrivacySettings();
    initPrivacyForm();
});
