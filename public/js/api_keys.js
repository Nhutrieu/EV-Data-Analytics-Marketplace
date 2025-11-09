document.addEventListener("DOMContentLoaded", () => {
    // Gọi qua router backend trong Docker
    const apiUrl = "/backend/data-consumer-service/index.php?page=api_key";

    const userId = window.USER_ID || 1;

    const listContainer = document.getElementById("apiKeyList");
    const createBtn = document.getElementById("createApiKeyBtn");

    if (!listContainer || !createBtn) return;

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
                    <div><strong>Key:</strong> ${key.api_key}</div>
                    <div><strong>Trạng thái:</strong> ${key.status}</div>
                    <div><strong>Ngày tạo:</strong> ${key.created_at}</div>
                    <button class="delete-api-btn">🗑 Xoá</button>
                `;
                listContainer.appendChild(div);

                // Gán sự kiện xoá (xoá theo user_id)
                const delBtn = div.querySelector(".delete-api-btn");
                delBtn.addEventListener("click", () => {
                    if (confirm("Bạn có chắc muốn xoá API key này không?")) {
                        deleteApiKey();
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
