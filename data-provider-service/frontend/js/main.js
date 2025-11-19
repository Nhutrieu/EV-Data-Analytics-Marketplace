// frontend/js/main.js

let currentFile = null;
let datasetsCache = [];

// Helper: format tiền VND
function formatVND(value) {
    if (value == null) return "-";
    return Number(value).toLocaleString("vi-VN") + " VND";
}

// Helper: map type -> text
function mapType(type) {
    switch (type) {
        case "battery": return "Dữ liệu pin";
        case "driving": return "Hành vi lái xe";
        case "charging": return "Sử dụng trạm sạc";
        case "v2g": return "Giao dịch V2G";
        default: return type || "-";
    }
}

// Helper: map format -> text
function mapFormat(fmt) {
    switch (fmt) {
        case "raw": return "Dữ liệu thô";
        case "analyzed": return "Đã phân tích";
        default: return fmt || "-";
    }
}

// Helper: map price_unit -> Hình thức (Mua / Thuê...)
function mapPriceUnit(unit) {
    switch (unit) {
        case "per-download":
            return "Mua theo lượt tải";
        case "subscription":
            return "Thuê theo tháng";
        case "one-time":
            return "Thuê theo năm";
        default:
            return "Không xác định";
    }
}

// ----------------- FILE UPLOAD UI -----------------

function initFileUpload() {
    const uploadArea = document.getElementById("file-upload-area");
    const fileInput = document.getElementById("data-file");
    const fileInfo = document.getElementById("file-info");

    if (!uploadArea || !fileInput) return;

    uploadArea.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", () => {
        if (fileInput.files && fileInput.files.length > 0) {
            currentFile = fileInput.files[0];
            showFileInfo(currentFile);
        }
    });

    uploadArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        uploadArea.classList.add("dragover");
    });

    uploadArea.addEventListener("dragleave", (e) => {
        e.preventDefault();
        uploadArea.classList.remove("dragover");
    });

    uploadArea.addEventListener("drop", (e) => {
        e.preventDefault();
        uploadArea.classList.remove("dragover");

        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            currentFile = files[0];
            fileInput.files = files;
            showFileInfo(currentFile);
        }
    });
}

function showFileInfo(file) {
    const fileInfo = document.getElementById("file-info");
    const fileNameSpan = document.getElementById("file-name");
    const fileSizeSpan = document.getElementById("file-size");

    if (!fileInfo || !fileNameSpan || !fileSizeSpan) return;

    fileNameSpan.textContent = file.name;
    fileSizeSpan.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
    fileInfo.style.display = "block";
}

function removeFile() {
    const fileInput = document.getElementById("data-file");
    const fileInfo = document.getElementById("file-info");
    currentFile = null;

    if (fileInput) fileInput.value = "";
    if (fileInfo) fileInfo.style.display = "none";
}

// ----------------- GIÁ MẶC ĐỊNH TỪ PRICING POLICY -----------------

async function initDefaultPricing() {
    const priceSpan = document.getElementById("default-price-display");
    const priceInput = document.getElementById("data-price");
    const priceUnitEl = document.getElementById("price-unit");

    if (!priceSpan && !priceInput) return; // không phải data.html

    try {
        const policy = await apiGetPricingPolicy(); // gọi backend

        if (priceSpan && policy.price != null) {
            const currency = policy.currency || "VND";
            priceSpan.textContent = formatVND(policy.price).replace("VND", currency);
        }

        if (priceInput && (priceInput.value === "" || priceInput.value === "0")) {
            if (policy.price != null) {
                priceInput.value = policy.price;
            }
        }

        if (priceUnitEl && policy.model) {
            const map = ["per-download", "subscription", "one-time"];
            if (map.includes(policy.model)) {
                priceUnitEl.value = policy.model;
            }
        }

    } catch (err) {
        console.error("Không load được chính sách giá mặc định:", err);
    }
}

// ----------------- FORM THÊM DỮ LIỆU -----------------

function initAddDataForm() {
    const form = document.getElementById("add-data-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        try {
            const name = document.getElementById("data-name").value.trim();
            const type = document.getElementById("data-type").value;
            const dataFormat = document.getElementById("data-format").value;
            const price = document.getElementById("data-price").value;
            const priceUnit = document.getElementById("price-unit").value;
            const description = document.getElementById("data-description").value.trim();
            const tags = document.getElementById("data-tags").value.trim();

            if (!name || !type || !dataFormat || price === "") {
                alert("Vui lòng điền đủ các trường bắt buộc.");
                return;
            }

            if (!currentFile) {
                alert("Vui lòng chọn file dữ liệu để tải lên.");
                return;
            }

            const payload = {
                name,
                type,
                format: dataFormat,
                price: Number(price),
                price_unit: priceUnit,
                description,
                status: "draft",
                admin_status: "pending",
                tags
            };

            const created = await apiCreateDataset(payload);
            const datasetId = created.id;

            await apiUploadDatasetFile(datasetId, currentFile);

            alert("Tạo dataset thành công và đã upload file!");
            resetForm();
            await loadDatasets();

        } catch (err) {
            console.error(err);
            alert(err.message || "Có lỗi xảy ra khi tạo dataset.");
        }
    });
}

function resetForm() {
    const form = document.getElementById("add-data-form");
    if (form) form.reset();
    removeFile();
}

// ----------------- LOAD & RENDER LIST -----------------

async function loadDatasets() {
    try {
        const searchInput = document.getElementById("search-data");
        const q = searchInput ? searchInput.value.trim() : "";
        const data = await apiGetDatasets(q);
        datasetsCache = data;
        renderDatasetTable(data);
    } catch (err) {
        console.error(err);
        alert("Không tải được danh sách dữ liệu.");
    }
}

function renderDatasetTable(datasets) {
    const tbody = document.getElementById("data-sources-body");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!datasets || datasets.length === 0) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 9; // 👈 9 cột: Name, Type, Format, File, Price, Hình thức, Status, Downloads, Actions
        td.textContent = "Chưa có dữ liệu nào.";
        td.style.textAlign = "center";
        td.style.color = "#64748b";
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    datasets.forEach(ds => {
        const tr = document.createElement("tr");

        // Tên bộ dữ liệu
        const tdName = document.createElement("td");
        tdName.textContent = ds.name || "-";

        // Loại dữ liệu
        const tdType = document.createElement("td");
        tdType.textContent = mapType(ds.type);

        // Định dạng (raw / analyzed)
        const tdFormat = document.createElement("td");
        tdFormat.textContent = mapFormat(ds.format);

        // Cột FILE NAME
        const tdFile = document.createElement("td");
        if (ds.file_name) {
            const span = document.createElement("span");
            span.textContent = ds.file_name;
            span.style.color = "#38bdf8";
            span.style.fontSize = "0.9rem";
            tdFile.appendChild(span);
        } else {
            tdFile.textContent = "(chưa upload)";
            tdFile.style.color = "#64748b";
            tdFile.style.fontStyle = "italic";
        }

        // Giá
        const tdPrice = document.createElement("td");
        tdPrice.textContent = formatVND(ds.price);

        // 👉 HÌNH THỨC (Mua / Thuê...)
        const tdPriceUnit = document.createElement("td");
        tdPriceUnit.textContent = mapPriceUnit(ds.price_unit);

        // Trạng thái
        const tdStatus = document.createElement("td");
        const status = ds.status || "draft";
        const spanStatus = document.createElement("span");
        spanStatus.textContent = status;
        spanStatus.className = "status-badge status-" + status;
        tdStatus.appendChild(spanStatus);

        // Lượt tải
        const tdDownloads = document.createElement("td");
        tdDownloads.textContent = ds.downloads ?? 0;

        // Thao tác
        const tdActions = document.createElement("td");
        tdActions.style.whiteSpace = "nowrap";

        const btnEdit = document.createElement("button");
        btnEdit.className = "btn btn-sm btn-outline";
        btnEdit.innerHTML = '<i class="fas fa-edit"></i>';
        btnEdit.title = "Chỉnh sửa";
        btnEdit.addEventListener("click", () => openEditModal(ds.id));

        const btnDelete = document.createElement("button");
        btnDelete.className = "btn btn-sm btn-outline";
        btnDelete.style.marginLeft = "0.5rem";
        btnDelete.innerHTML = '<i class="fas fa-trash"></i>';
        btnDelete.title = "Xoá";
        btnDelete.addEventListener("click", () => handleDeleteDataset(ds.id));

        tdActions.appendChild(btnEdit);
        tdActions.appendChild(btnDelete);

        // Thứ tự cột phải khớp với <thead>
        tr.appendChild(tdName);
        tr.appendChild(tdType);
        tr.appendChild(tdFormat);
        tr.appendChild(tdFile);
        tr.appendChild(tdPrice);
        tr.appendChild(tdPriceUnit);   // 👈 thêm cột Hình thức
        tr.appendChild(tdStatus);
        tr.appendChild(tdDownloads);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
    });
}

async function handleDeleteDataset(id) {
    if (!confirm("Bạn có chắc chắn muốn xoá bộ dữ liệu này?")) return;
    try {
        await apiDeleteDataset(id);
        alert("Đã xoá dataset.");
        await loadDatasets();
    } catch (err) {
        console.error(err);
        alert(err.message || "Không xoá được dataset.");
    }
}

// ----------------- SEARCH & REFRESH -----------------

function initSearch() {
    const searchInput = document.getElementById("search-data");
    if (!searchInput) return;
    searchInput.addEventListener("keyup", (e) => {
        if (e.key === "Enter") {
            loadDatasets();
        }
    });
}

function refreshData() {
    loadDatasets();
}

// ----------------- MODAL EDIT -----------------

function openEditModal(id) {
    const ds = datasetsCache.find(d => d.id == id);
    if (!ds) {
        alert("Không tìm thấy dataset.");
        return;
    }

    const modal = document.getElementById("edit-modal");
    if (!modal) return;

    document.getElementById("edit-data-id").value = ds.id;
    document.getElementById("edit-data-name").value = ds.name || "";
    document.getElementById("edit-data-type").value = ds.type || "battery";
    document.getElementById("edit-data-format").value = ds.format || "raw";
    document.getElementById("edit-data-price").value = ds.price ?? 0;
    document.getElementById("edit-price-unit").value = ds.price_unit || "per-download";
    document.getElementById("edit-data-description").value = ds.description || "";

    const statusInfo = document.getElementById("status-info");
    if (statusInfo) {
        statusInfo.innerHTML = `
            <p><strong>Trạng thái:</strong> ${ds.status || "draft"}</p>
            <p><strong>Trạng thái admin:</strong> ${ds.admin_status || "pending"}</p>
            <p><strong>Ghi chú admin:</strong> ${ds.admin_note || "-"}</p>
        `;
    }

    modal.style.display = "flex";
}

function closeEditModal() {
    const modal = document.getElementById("edit-modal");
    if (modal) modal.style.display = "none";
}

function initEditForm() {
    const form = document.getElementById("edit-data-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        try {
            const id = document.getElementById("edit-data-id").value;
            const name = document.getElementById("edit-data-name").value.trim();
            const type = document.getElementById("edit-data-type").value;
            const format = document.getElementById("edit-data-format").value;
            const price = document.getElementById("edit-data-price").value;
            const priceUnit = document.getElementById("edit-price-unit").value;
            const description = document.getElementById("edit-data-description").value.trim();

            const payload = {
                name,
                type,
                format,
                price: Number(price),
                price_unit: priceUnit,
                description
            };

            await apiUpdateDataset(id, payload);
            alert("Đã lưu thay đổi.");
            closeEditModal();
            await loadDatasets();
        } catch (err) {
            console.error(err);
            alert(err.message || "Không lưu được thay đổi.");
        }
    });
}

// ----------------- USER DROPDOWN DUMMY -----------------

function initUserDropdown() {
    const userInfo = document.getElementById("user-info-dropdown");
    const dropdown = document.getElementById("user-dropdown");
    if (!userInfo || !dropdown) return;

    userInfo.addEventListener("click", () => {
        const isOpen = dropdown.style.display === "block";
        dropdown.style.display = isOpen ? "none" : "block";
    });

    document.addEventListener("click", (e) => {
        if (!userInfo.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
}

function showProfile() {
    alert("Chức năng hồ sơ sẽ phát triển sau.");
}
function showSettings() {
    alert("Chức năng cài đặt sẽ phát triển sau.");
}
function logout() {
    alert("Giả lập logout.");
}

// ----------------- INIT -----------------

document.addEventListener("DOMContentLoaded", () => {
    initFileUpload();
    initDefaultPricing();   // load chính sách giá mặc định
    initAddDataForm();
    initSearch();
    initEditForm();
    initUserDropdown();
    loadDatasets();
});

window.refreshData = refreshData;
window.removeFile = removeFile;
window.closeEditModal = closeEditModal;
window.showProfile = showProfile;
window.showSettings = showSettings;
window.logout = logout;
