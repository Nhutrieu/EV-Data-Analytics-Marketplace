// frontend/js/pricing.js

const DEFAULT_LICENSE_TEXT =
    "Dữ liệu được cung cấp bởi EV Data Analytics Marketplace. " +
    "Người mua được phép sử dụng dữ liệu cho mục đích đã được chỉ định. " +
    "Không được phép phân phối lại, bán lại, hoặc sử dụng cho mục đích bất hợp pháp. " +
    "Bản quyền dữ liệu thuộc về EV Data Corp.";

// Load pricing policy id=1 vào form
async function loadPricingForm() {
    try {
        const policy = await apiGetPricingPolicy(); // id=1

        const modelSelect = document.getElementById("pricing-model");
        const priceInput = document.getElementById("price-value");
        const currencySel = document.getElementById("currency");
        const usageSel = document.getElementById("usage-rights");
        const licenseTa = document.getElementById("license-terms");

        if (modelSelect && policy.model) modelSelect.value = policy.model;
        if (priceInput && policy.price != null) priceInput.value = policy.price;
        if (currencySel && policy.currency) currencySel.value = policy.currency;
        if (usageSel && policy.usage_rights) usageSel.value = policy.usage_rights;

        if (licenseTa) {
            if (policy.license_terms && policy.license_terms.trim() !== "") {
                licenseTa.value = policy.license_terms;
            } else {
                licenseTa.value = DEFAULT_LICENSE_TEXT;
            }
        }

    } catch (err) {
        console.error(err);
        alert(err.message || "Không load được chính sách giá từ server.");
    }
}

// Lưu lại policy id=1 từ form
async function savePricingPolicy() {
    const modelSelect = document.getElementById("pricing-model");
    const priceInput = document.getElementById("price-value");
    const currencySel = document.getElementById("currency");
    const usageSel = document.getElementById("usage-rights");
    const licenseTa = document.getElementById("license-terms");

    const payload = {
        model: modelSelect ? modelSelect.value : null,
        price: priceInput ? Number(priceInput.value) : 0,
        currency: currencySel ? currencySel.value : "VND",
        usage_rights: usageSel ? usageSel.value : null,
        license_terms: licenseTa ? licenseTa.value : null,
    };

    if (!payload.model || !payload.price) {
        alert("Vui lòng nhập đầy đủ mô hình định giá và giá.");
        return;
    }

    try {
        const res = await apiUpdatePricingPolicy(1, payload);
        alert(res.message || "Đã lưu chính sách giá & điều khoản sử dụng.");
    } catch (err) {
        console.error(err);
        alert(err.message || "Không lưu được chính sách giá.");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    loadPricingForm();

    const pricingForm = document.getElementById("pricing-form");
    const termsForm = document.getElementById("terms-form");

    if (pricingForm) {
        pricingForm.addEventListener("submit", (e) => {
            e.preventDefault();
            savePricingPolicy();
        });
    }

    if (termsForm) {
        termsForm.addEventListener("submit", (e) => {
            e.preventDefault();
            savePricingPolicy();
        });
    }
});
