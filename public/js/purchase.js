// ===========================
// Purchase Module
// ===========================
async function loadUserPurchases() {
    try {
        const res = await fetch("/EV-Data-Analytics-Marketplace/backend/data-consumer-service/index.php?page=purchase", {
            credentials: 'include'
        });

        const data = await res.json();
        if (data.success) return data.data; // array of purchases
        return [];
    } catch (err) {
        console.error(err);
        return [];
    }
}

// ===========================
// Hiển thị nút tải xuống trong modal chi tiết
// ===========================
async function handleModalDetail(datasetId) {
    const purchases = await loadUserPurchases();
    const today = new Date();
    const purchase = purchases.find(p => p.dataset_id === datasetId);

    let canDownload = false;
    let purchaseType = null;

    if (purchase) {
        if (purchase.type === "Mua") {
            canDownload = true;
            purchaseType = "Mua";
        } else if (purchase.expiry_date) {
            const expiry = new Date(purchase.expiry_date);
            if (expiry >= today) {
                canDownload = true;
                purchaseType = purchase.type;
            }
        }
    }

    const modalContent = document.getElementById("detailContent");
    const existingBtn = document.getElementById("downloadBtn");
    if (existingBtn) existingBtn.remove();

    if (canDownload) {
        const btn = document.createElement("button");
        btn.id = "downloadBtn";
        btn.className = "btn btn-success";
        btn.textContent = "Tải xuống";
        btn.onclick = () => downloadDataset(datasetId, purchaseType);
        modalContent.appendChild(btn);
    }
}

// ===========================
// Download dataset
// ===========================
function downloadDataset(datasetId, type) {
    alert(`Bắt đầu tải dataset ID ${datasetId} (${type})`);
    // TODO: gọi API backend thực tế nếu có
}
