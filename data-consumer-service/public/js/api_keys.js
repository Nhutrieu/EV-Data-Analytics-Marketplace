document.addEventListener("DOMContentLoaded", () => {
    // Gọi qua router backend (nằm trong backend/data-consumer-service)
    const apiUrl = "/index.php?page=api_key";

    const listContainer = document.getElementById("apiKeyList");
    const createBtn = document.getElementById("createApiKeyBtn");

    if (!listContainer || !createBtn) return;

    // Dùng toast chung nếu có
    function showToast(message) {
        const toast = document.getElementById("toast");
        if (!toast) {
            alert(message);
            return;
        }
        toast.textContent = message;
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 2500);
    }

    // 🔹 Hàm che key: hiện 4 ký tự đầu, còn lại chấm
    function maskKey(k) {
        if (!k) return "";
        const visible = 4;
        const len = k.length;
        if (len <= visible) return "•".repeat(len);
        const maskedPart = "•".repeat(len - visible);
        return k.slice(0, visible) + " " + maskedPart;
    }

    // 🔹 Load API key hiện tại của user đang login (server tự biết qua session)
    async function loadApiKeys() {
        try {
            const res = await fetch(`${apiUrl}&action=list`, {
                credentials: "include",
            });

            // Nếu chưa login → backend trả 401
            if (res.status === 401) {
                listContainer.innerHTML =
                    `<p>Bạn chưa đăng nhập. <a href="/login.php">Đăng nhập</a></p>`;
                return;
            }

            const data = await res.json();
            listContainer.innerHTML = "";

            if (!data.success) {
                listContainer.innerHTML =
                    `<p>Lỗi tải API key: ${data.message || ""}</p>`;
                return;
            }

            const key = data.data; // backend trả 1 object hoặc null

            if (!key) {
                listContainer.innerHTML = `<p>Chưa có API key nào.</p>`;
                return;
            }

            // Hiển thị 1 key duy nhất
            const div = document.createElement("div");
            div.classList.add("api-key-row");
            div.innerHTML = `
                <div class="api-key-row-main">
                    <div class="api-key-left">
                        <strong>Key:</strong>
                        <span class="api-key-value"
                              data-full="${key.api_key}"
                              data-visible="0">
                            ${maskKey(key.api_key)}
                        </span>
                    </div>
                    <button type="button"
                            class="toggle-api-visibility material-symbols-outlined"
                            aria-label="Ẩn/hiện API key">
                        visibility
                    </button>
                </div>
                <div class="api-key-meta">
                    <div><strong>Trạng thái:</strong> ${key.status}</div>
                    <div><strong>Ngày tạo:</strong> ${key.created_at}</div>
                </div>
                <div class="api-key-actions">
                    <button class="delete-api-btn">Xoá</button>
                </div>
            `;
            listContainer.appendChild(div);

            // Nút Xoá
            const delBtn = div.querySelector(".delete-api-btn");
            delBtn.addEventListener("click", () => {
                if (confirm("Bạn có chắc muốn xoá API key này không?")) {
                    deleteApiKey();
                }
            });

            // Nút mắt ẩn/hiện
            const toggleBtn = div.querySelector(".toggle-api-visibility");
            const valueSpan = div.querySelector(".api-key-value");

            toggleBtn.addEventListener("click", () => {
                const fullKey = valueSpan.dataset.full;
                const isShown = valueSpan.dataset.visible === "1";

                if (isShown) {
                    // Đang hiện → che lại
                    valueSpan.textContent = maskKey(fullKey);
                    valueSpan.dataset.visible = "0";
                    toggleBtn.textContent = "visibility_off"; // mắt gạch
                } else {
                    // Đang che → hiện full
                    valueSpan.textContent = fullKey;
                    valueSpan.dataset.visible = "1";
                    toggleBtn.textContent = "visibility"; // mắt mở
                }
            });
        } catch (err) {
            console.error("Lỗi tải API keys:", err);
            listContainer.innerHTML =
                "<p>Lỗi khi tải API key. Xem console để biết thêm chi tiết.</p>";
        }
    }

    // 🔹 Tạo API key mới
    createBtn.addEventListener("click", async () => {
        try {
            const res = await fetch(`${apiUrl}&action=create`, {
                credentials: "include",
            });

            if (res.status === 401) {
                showToast("Bạn chưa đăng nhập. Vui lòng đăng nhập lại.");
                window.location.href = "/login.php";
                return;
            }

            const data = await res.json();

            if (data.success) {
                alert("Tạo API key thành công!\nKey: " + data.api_key);

                // Lưu FULL API key vào localStorage để dùng sau
                try {
                    localStorage.setItem("EV_API_KEY", data.api_key);
                } catch (e) {
                    console.warn("Không lưu được API key vào localStorage:", e);
                }

                loadApiKeys();
            } else {
                showToast("Không thể tạo API key: " + (data.message || ""));
            }
        } catch (err) {
            console.error("Lỗi tạo API key:", err);
            showToast("Có lỗi khi gọi server để tạo API key.");
        }
    });

    // 🔹 Xoá API key hiện tại của user
    async function deleteApiKey() {
        try {
            const res = await fetch(`${apiUrl}&action=delete`, {
                credentials: "include",
            });

            if (res.status === 401) {
                showToast("Bạn chưa đăng nhập. Vui lòng đăng nhập lại.");
                window.location.href = "/login.php";
                return;
            }

            const data = await res.json();
            showToast(data.message || "Đã xử lý yêu cầu xoá API key.");

            if (data.success) {
                try {
                    localStorage.removeItem("EV_API_KEY");
                } catch (e) {
                    console.warn(
                        "Không xoá được API key khỏi localStorage:",
                        e
                    );
                }
                loadApiKeys();
            }
        } catch (err) {
            console.error("Lỗi xoá API key:", err);
            showToast("Có lỗi khi xoá API key trên server.");
        }
    }

    // Gọi lần đầu
    loadApiKeys();
});
