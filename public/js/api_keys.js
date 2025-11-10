document.addEventListener("DOMContentLoaded", () => {
    // Gọi qua router backend trong Docker
    const apiUrl = "/backend/data-consumer-service/index.php?page=api_key";

    const userId = window.USER_ID || 1;

    const listContainer = document.getElementById("apiKeyList");
    const createBtn = document.getElementById("createApiKeyBtn");

    if (!listContainer || !createBtn) return;

    // 🔹 Hàm che key: hiện 4 ký tự đầu, còn lại chấm
    function maskKey(k) {
        if (!k) return "";
        const visible = 4;
        const len = k.length;
        if (len <= visible) return "•".repeat(len);
        const maskedPart = "•".repeat(len - visible);
        return k.slice(0, visible) + " " + maskedPart;
    }

    // 🔹 Load API key hiện tại của user
    function loadApiKeys() {
        fetch(`${apiUrl}&action=list&user_id=${userId}`)
            .then((res) => res.json())
            .then((data) => {
                listContainer.innerHTML = "";

                if (!data.success) {
                    listContainer.innerHTML = `<p>Lỗi tải API key: ${data.message || ""}</p>`;
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
                                  data-visible="1">
                                ${key.api_key}
                            </span>
                        </div>
                        <button type="button"
                                class="toggle-api-visibility material-symbols-outlined"
                                aria-label="Ẩn/hiện API key">
                            visibility
                        </button>
                    </div>
                    <div><strong>Trạng thái:</strong> ${key.status}</div>
                    <div><strong>Ngày tạo:</strong> ${key.created_at}</div>
                    <button class="delete-api-btn">Xoá</button>
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
            })
            .catch((err) => {
                console.error("Lỗi tải API keys:", err);
                listContainer.innerHTML =
                    "<p>Lỗi khi tải API key. Xem console để biết thêm chi tiết.</p>";
            });
    }

    // 🔹 Tạo API key mới
    createBtn.addEventListener("click", () => {
        fetch(`${apiUrl}&action=create&user_id=${userId}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    alert("Tạo API key thành công!\nKey: " + data.api_key);

                    // Lưu FULL API key vào localStorage để dùng sau (truy cập bên thứ ba)
                    try {
                        localStorage.setItem("EV_API_KEY", data.api_key);
                    } catch (e) {
                        console.warn("Không lưu được API key vào localStorage:", e);
                    }

                    loadApiKeys();
                } else {
                    alert("Không thể tạo API key: " + data.message);
                }
            })
            .catch((err) => {
                console.error("Lỗi tạo API key:", err);
            });
    });

    // 🔹 Xoá API key hiện tại của user
    function deleteApiKey() {
        fetch(`${apiUrl}&action=delete&user_id=${userId}`)
            .then((res) => res.json())
            .then((data) => {
                alert(data.message || "Đã xử lý yêu cầu xoá API key.");
                if (data.success) {
                    // Xoá luôn localStorage nếu có
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
            })
            .catch((err) => {
                console.error("Lỗi xoá API key:", err);
            });
    }

    // Gọi lần đầu
    loadApiKeys();
});
