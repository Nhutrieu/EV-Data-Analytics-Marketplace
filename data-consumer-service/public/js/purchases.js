// =======================================
// purchase.js
// Hiển thị thông tin purchase + nút Tải xuống / Truy cập API
// =======================================

// Lấy danh sách purchases của user hiện tại
async function loadUserPurchases() {
    try {
        // Gọi thẳng vào router backend trong Docker
        const res = await fetch(
            "/index.php?page=purchase",
            {
                credentials: "include", // gửi theo session cookie
            }
        );

        if (res.status === 401) {
            console.warn("Chưa login, không load được danh sách purchases.");
            return [];
        }

        const data = await res.json();
        if (data.success && Array.isArray(data.data)) {
            return data.data; // array các purchases
        }

        console.warn("loadUserPurchases: response không như mong đợi:", data);
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


    modalContent.appendChild(infoWrapper);
}

// ===========================
// Tải dataset (file/local backend)
// ===========================
function downloadDataset(datasetId, type) {
    // Backend download + kiểm tra quyền bằng session / purchases
    window.location.href =
        `/api/download_dataset.php?dataset_id=${datasetId}`;
}

