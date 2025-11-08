// =======================================
// purchase.js
// Hiển thị thông tin purchase + nút Tải xuống / Truy cập API
// =======================================

// Lấy danh sách purchases của user hiện tại
async function loadUserPurchases() {
    try {
        // Lấy theo USER_ID đang dùng bên frontend
        const res = await fetch(
            `/EV-Data-Analytics-Marketplace/backend/data-consumer-service/index.php?page=purchase&user_id=${USER_ID}`,
            {
                credentials: "include",
            }
        );

        const data = await res.json();
        if (data.success && Array.isArray(data.data)) {
            return data.data; // array các purchases
        }
        return [];
    } catch (err) {
        console.error("loadUserPurchases error:", err);
        return [];
    }
}

// ===========================
// Gắn thêm block purchase + nút tải xuống / API
// ===========================
async function handleModalDetail(datasetId) {
    const purchases = await loadUserPurchases();
    const today = new Date();

    // dataset_id trong DB có thể là string → ép về số
    const purchase = purchases.find(
        (p) => Number(p.dataset_id) === Number(datasetId)
    );

    const modalContent = document.getElementById("detailContent");
    if (!modalContent) return;

    // ❗ CHỈ xoá block purchaseInfo cũ, KHÔNG đụng phần chi tiết dataset
    const oldInfo = document.getElementById("purchaseInfo");
    if (oldInfo) oldInfo.remove();

    const infoWrapper = document.createElement("div");
    infoWrapper.id = "purchaseInfo";
    infoWrapper.style.marginTop = "16px";
    infoWrapper.style.borderTop = "1px solid #eee";
    infoWrapper.style.paddingTop = "12px";

    // Chưa từng mua/thuê dataset này
    if (!purchase) {
        infoWrapper.textContent = "Bạn chưa mua/thuê dataset này.";
        modalContent.appendChild(infoWrapper);
        return;
    }

    // ----- Thông tin cơ bản của purchase -----
    const purchasedAtText = purchase.purchased_at
        ? new Date(purchase.purchased_at).toLocaleDateString("vi-VN")
        : "Chưa cập nhật";

    const expiryText = purchase.expiry_date
        ? new Date(purchase.expiry_date).toLocaleDateString("vi-VN")
        : "Không có (mua vĩnh viễn hoặc chưa set)";

    infoWrapper.innerHTML = `
        <p><strong>Trạng thái thanh toán:</strong> ${purchase.status}</p>
        <p><strong>Loại gói:</strong> ${purchase.type}</p>
        <p><strong>Ngày mua:</strong> ${purchasedAtText}</p>
        <p><strong>Hết hạn:</strong> ${expiryText}</p>
        <p><em>Để truy cập API bên thứ ba, hệ thống sẽ tự dùng API key gắn với tài khoản của bạn.</em></p>
    `;

    // 1. Nếu chưa paid → chỉ show cảnh báo
    if (purchase.status !== "paid") {
        const p = document.createElement("p");
        p.style.color = "red";
        p.style.marginTop = "8px";
        p.textContent =
            "Thanh toán chưa hoàn tất hoặc đang chờ xác nhận. Vui lòng thanh toán xong để tải / truy cập dataset.";
        infoWrapper.appendChild(p);
        modalContent.appendChild(infoWrapper);
        return;
    }

    // 2. Kiểm tra hạn sử dụng với gói thuê
    let canUse = false;

    // Mua vĩnh viễn
    if (purchase.type === "Mua" || purchase.type === "buy") {
        canUse = true;
    } else {
        // Thuê: chỉ cho nếu chưa hết hạn
        if (
            purchase.expiry_date &&
            new Date(purchase.expiry_date) >= today
        ) {
            canUse = true;
        }
    }

    if (!canUse) {
        const p = document.createElement("p");
        p.style.color = "red";
        p.style.marginTop = "8px";
        p.textContent =
            "Gói thuê đã hết hạn, bạn không thể tải hoặc truy cập dataset này nữa.";
        infoWrapper.appendChild(p);
        modalContent.appendChild(infoWrapper);
        return;
    }

    // ----- Nút TẢI XUỐNG -----
    const downloadBtn = document.createElement("button");
    downloadBtn.id = "downloadBtn";
    downloadBtn.className = "btn btn-success";
    downloadBtn.style.marginTop = "10px";
    downloadBtn.textContent = "Tải xuống";

    downloadBtn.onclick = () => downloadDataset(datasetId, purchase.type);
    infoWrapper.appendChild(downloadBtn);

    // ----- Nút TRUY CẬP DỮ LIỆU (API) -----
    const apiBtn = document.createElement("button");
    apiBtn.className = "btn btn-primary";
    apiBtn.style.marginTop = "10px";
    apiBtn.style.marginLeft = "8px";
    apiBtn.textContent = "Truy cập dữ liệu (API)";

    apiBtn.onclick = () => accessDatasetWithApiKey(datasetId);
    infoWrapper.appendChild(apiBtn);

    modalContent.appendChild(infoWrapper);
}

// ===========================
// Tải dataset (file/local backend)
// ===========================
function downloadDataset(datasetId, type) {
    // Backend download + kiểm tra quyền bằng session / purchases
    window.location.href =
        `/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/download_dataset.php?dataset_id=${datasetId}`;
}

// ===========================
// Helper: Lấy (hoặc tạo) API key từ server cho user hiện tại
// ===========================
async function getOrCreateApiKeyFromServer() {
    try {
        const res = await fetch(
            "/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/get_api_key.php",
            {
                method: "GET",
                credentials: "include",
            }
        );

        const data = await res.json();
        if (!data.success || !data.api_key) {
            console.error("getOrCreateApiKeyFromServer error:", data);
            return null;
        }

        // Lưu full API key vào localStorage để dùng nhanh lần sau
        try {
            localStorage.setItem("EV_API_KEY", data.api_key);
        } catch (e) {
            console.warn("Không lưu được API key vào localStorage:", e);
        }

        return data.api_key;
    } catch (err) {
        console.error("getOrCreateApiKeyFromServer error:", err);
        return null;
    }
}

// ===========================
// Truy cập dữ liệu bên thứ ba bằng API key
// ===========================
async function accessDatasetWithApiKey(datasetId) {
    let apiKey = null;

    // 1. Thử lấy từ localStorage trước
    try {
        apiKey = localStorage.getItem("EV_API_KEY");
    } catch (e) {
        console.warn("Không đọc được EV_API_KEY từ localStorage:", e);
    }

    // 2. Nếu chưa có → gọi server (tự tạo nếu chưa tồn tại)
    if (!apiKey) {
        apiKey = await getOrCreateApiKeyFromServer();
    }

    if (!apiKey) {
        if (typeof showToast === "function") {
            showToast(
                "❌ Không lấy được API key. Vui lòng tạo API key trong mục Tài khoản."
            );
        } else {
            alert(
                "Không lấy được API key. Vui lòng tạo API key trong mục Tài khoản."
            );
        }
        return;
    }

    try {
        const res = await fetch(
            `/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/data_access.php?dataset_id=${datasetId}`,
            {
                method: "GET",
                headers: {
                    Authorization: "Bearer " + apiKey,
                    "Content-Type": "application/json",
                },
            }
        );

        const data = await res.json();

        if (data.success) {
            console.log("Dữ liệu từ API bên thứ ba:", data.data);
            if (typeof showToast === "function") {
                showToast("✅ Dữ liệu bên thứ ba đã sẵn sàng (xem console).");
            } else {
                alert("✅ Dữ liệu bên thứ ba đã sẵn sàng. Xem console để debug.");
            }
        } else {
            const msg =
                data.message || "Không truy cập được dữ liệu bên thứ ba";
            if (typeof showToast === "function") {
                showToast("❌ " + msg);
            } else {
                alert("❌ " + msg);
            }
        }
    } catch (err) {
        console.error("accessDatasetWithApiKey error:", err);
        if (typeof showToast === "function") {
            showToast("❌ Lỗi khi truy cập dữ liệu bên thứ ba");
        } else {
            alert("❌ Lỗi khi truy cập dữ liệu bên thứ ba");
        }
    }
}
