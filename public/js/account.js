document.addEventListener("DOMContentLoaded", () => {
    const purchaseList = document.getElementById("purchaseList");
    if (!purchaseList) return;

    loadUserPurchases();

    async function loadUserPurchases() {
        try {
            const res = await fetch(
                "/backend/data-consumer-service/index.php?page=purchase",
                {
                    method: "GET",
                    credentials: "include", // để gửi kèm cookie / session
                }
            );

            const data = await res.json();

            if (!data.success) {
                purchaseList.innerHTML =
                    `<p class="purchase-empty">Lỗi tải lịch sử mua: ${data.message || ""}</p>`;
                return;
            }

            const purchases = data.data || [];

            if (!purchases.length) {
                purchaseList.innerHTML =
                    `<p class="purchase-empty">Bạn chưa mua hoặc thuê gói dữ liệu nào.</p>`;
                return;
            }

            purchaseList.innerHTML = purchases
                .map((p) => {
                    const purchasedAt = p.purchased_at
                        ? new Date(p.purchased_at).toLocaleString("vi-VN")
                        : "Chưa cập nhật";

                    const expiryText = p.expiry_date
                        ? new Date(p.expiry_date).toLocaleDateString("vi-VN")
                        : (p.type === "Mua" ? "Vĩnh viễn" : "Không rõ");

                    const priceVND = Number(p.price || 0).toLocaleString("vi-VN");

                    return `
                        <div class="purchase-item">
                            <div class="purchase-item-header">
                                <span class="purchase-title">
                                    Dataset #${p.dataset_id}
                                </span>
                                <span class="purchase-type">
                                    ${p.type}
                                </span>
                            </div>
                            <div class="purchase-meta">
                                <span><strong>Trạng thái:</strong> ${p.status}</span>
                                <span><strong>Ngày mua:</strong> ${purchasedAt}</span>
                                <span><strong>Hết hạn:</strong> ${expiryText}</span>
                                <span><strong>Số tiền:</strong> ${priceVND} VNĐ</span>
                            </div>
                        </div>
                    `;
                })
                .join("");
        } catch (err) {
            console.error("loadUserPurchases error:", err);
            purchaseList.innerHTML =
                `<p class="purchase-empty">Có lỗi khi tải lịch sử mua. Xem console để biết thêm.</p>`;
        }
    }
});
