// frontend/js/api.js

// 🔗 Đường dẫn tuyệt đối tới backend provider service
const API_BASE = "/index.php";

/**
 * Lấy danh sách datasets
 */
async function apiGetDatasets(q = "") {
    const params = new URLSearchParams();
    params.append("page", "datasets");
    if (q) params.append("q", q);

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url);
    const text = await res.text();

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("❌ GET /datasets không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }
}

/**
 * Tạo dataset mới
 */
async function apiCreateDataset(payload) {
    const params = new URLSearchParams();
    params.append("page", "datasets");

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ POST /datasets không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    console.log("📥 POST /datasets raw:", text);

    if (!res.ok) {
        throw new Error(data.message || "Không tạo được dataset");
    }
    return data; // { id, message }
}

/**
 * Cập nhật dataset
 */
async function apiUpdateDataset(id, payload) {
    const params = new URLSearchParams();
    params.append("page", "datasets");
    params.append("id", id);

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ PUT /datasets không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok) {
        throw new Error(data.message || "Không cập nhật được dataset");
    }
    return data;
}

/**
 * Xoá dataset
 */
async function apiDeleteDataset(id) {
    const params = new URLSearchParams();
    params.append("page", "datasets");
    params.append("id", id);

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url, {
        method: "DELETE",
    });

    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ DELETE /datasets không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok) {
        throw new Error(data.message || "Không xoá được dataset");
    }
    return data;
}

/**
 * Upload file cho dataset
 */
async function apiUploadDatasetFile(id, file) {
    const params = new URLSearchParams();
    params.append("page", "datasets");
    params.append("id", id);
    params.append("action", "upload");

    const url = `${API_BASE}?${params.toString()}`;

    const formData = new FormData();
    formData.append("file", file);

    const res = await fetch(url, {
        method: "POST",
        body: formData,
    });

    const text = await res.text();
    console.log("📥 POST /datasets upload raw:", text);

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ POST /datasets upload không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok) {
        throw new Error(data.message || "Không upload được file");
    }
    return data;
}

// ================== PRICING API ==================

/**
 * Lấy chính sách giá mặc định (id=1)
 */
async function apiGetPricingPolicy() {
    const params = new URLSearchParams();
    params.append("page", "pricing_api");
    params.append("id", 1);

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url);
    const text = await res.text();

    console.log("📥 GET /pricing_api raw:", text);

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ GET /pricing_api không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok) {
        throw new Error(data.message || "Không lấy được chính sách giá");
    }
    return data;
}

/**
 * Cập nhật chính sách giá (id bất kỳ, thường dùng id=1)
 */
async function apiUpdatePricingPolicy(id, payload) {
    const params = new URLSearchParams();
    params.append("page", "pricing_api");
    params.append("id", id);

    const url = `${API_BASE}?${params.toString()}`;

    const res = await fetch(url, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    const text = await res.text();
    console.log("📥 PUT /pricing_api raw:", text);

    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ PUT /pricing_api không phải JSON. Response:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok) {
        throw new Error(data.message || "Không cập nhật được chính sách giá");
    }
    return data;
}

// Expose ra global (optional)
window.apiGetDatasets = apiGetDatasets;
window.apiCreateDataset = apiCreateDataset;
window.apiUpdateDataset = apiUpdateDataset;
window.apiDeleteDataset = apiDeleteDataset;
window.apiUploadDatasetFile = apiUploadDatasetFile;
window.apiGetPricingPolicy = apiGetPricingPolicy;
window.apiUpdatePricingPolicy = apiUpdatePricingPolicy;

/**
 * Lấy dashboard doanh thu (summary + by_dataset + monthly)
 */
async function apiGetRevenueDashboard(params = {}) {
    const urlParams = new URLSearchParams();
    urlParams.append("page", "revenue_api");

    if (params.from) urlParams.append("from", params.from);
    if (params.to) urlParams.append("to", params.to);

    const url = `${API_BASE}?${urlParams.toString()}`;
    const res = await fetch(url);
    const text = await res.text();

    try {
        const data = JSON.parse(text);
        return data;
    } catch (e) {
        console.error("❌ GET /revenue_api không phải JSON. Status:", res.status);
        console.error("Raw response:", text);
        throw new Error("API doanh thu trả về dữ liệu không phải JSON");
    }
}
// Lấy cài đặt bảo mật
async function apiGetPrivacySettings() {
    const url = `${API_BASE}?page=privacy_api`;
    const res = await fetch(url);
    const text = await res.text();

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("❌ GET privacy_api không phải JSON:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }
}

// Lưu cài đặt bảo mật
async function apiUpdatePrivacySettings(payload) {
    const url = `${API_BASE}?page=privacy_api`;
    const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    const text = await res.text();
    let data;
    try {
        data = JSON.parse(text);
    } catch (e) {
        console.error("❌ POST privacy_api không phải JSON:", text);
        throw new Error("API trả về dữ liệu không phải JSON");
    }

    if (!res.ok || data.success === false) {
        throw new Error(data.message || "Không lưu được cài đặt bảo mật");
    }
    return data;
}
