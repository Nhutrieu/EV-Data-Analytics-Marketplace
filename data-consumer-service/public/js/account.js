// =======================
// LỊCH SỬ MUA GÓI (purchaseList)
// =======================
document.addEventListener("DOMContentLoaded", () => {
    const purchaseList = document.getElementById("purchaseList");
    if (!purchaseList) return;

    loadUserPurchases();

    async function loadUserPurchases() {
        try {
            const res = await fetch(
                "/index.php?page=purchase",
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


// =======================
// TÀI KHOẢN: hồ sơ, mật khẩu, logout, xoá
// =======================
document.addEventListener('DOMContentLoaded', () => {
    // Gọi về router index.php
    const ACCOUNT_API_URL = '/index.php?page=account';

    // Toast / notify
    function notify(msg) {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        } else {
            alert(msg);
        }
    }

    // Mở / đóng modal
    function openAccountModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('show');
    }

    function closeAccountModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('show');
    }

    // Event cho close & backdrop
    document.querySelectorAll('.account-modal [data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-close');
            if (id) closeAccountModal(id);
        });
    });

    // ---------- CHỈNH SỬA HỒ SƠ ----------
    const editProfileBtn = document.getElementById('editProfileBtn');
    const profileForm = document.getElementById('profileForm');

    if (editProfileBtn && profileForm) {
        editProfileBtn.addEventListener('click', () => {
            // cố gắng đọc từ profile-name / profile-email nếu có
            const nameEl = document.querySelector('.profile-info .profile-name') ||
                document.querySelector('.profile-info h2');
            const emailEl = document.querySelector('.profile-info .profile-email');

            const nameVal = nameEl ? nameEl.textContent.trim() : '';
            const emailVal = emailEl ? emailEl.textContent.trim() : '';

            const nameInput = document.getElementById('profileName');
            const emailInput = document.getElementById('profileEmail');

            if (nameInput) nameInput.value = nameVal;
            if (emailInput) emailInput.value = emailVal;

            openAccountModal('profileModal');
        });

        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('profileName').value.trim();
            const email = document.getElementById('profileEmail').value.trim();

            const body = new URLSearchParams({
                action: 'update_profile',
                name,
                email
            });

            const submitBtn = profileForm.querySelector('.btn-account.primary');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang lưu...';
            }

            try {
                const res = await fetch(ACCOUNT_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });
                const data = await res.json();
                notify(data.message || '');

                if (data.success && data.data) {
                    // cập nhật text hiển thị trên UI
                    const nameEl = document.querySelector('.profile-info .profile-name') ||
                        document.querySelector('.profile-info h2');
                    const emailEl = document.querySelector('.profile-info .profile-email');
                    if (nameEl) nameEl.textContent = data.data.name;
                    if (emailEl) emailEl.textContent = data.data.email;

                    closeAccountModal('profileModal');
                }
            } catch (err) {
                console.error(err);
                notify('Có lỗi xảy ra. Vui lòng thử lại.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Lưu thay đổi';
                }
            }
        });
    }

    // ---------- ĐỔI MẬT KHẨU ----------
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const passwordForm = document.getElementById('passwordForm');

    if (changePasswordBtn && passwordForm) {
        changePasswordBtn.addEventListener('click', () => {
            passwordForm.reset();
            openAccountModal('passwordModal');
        });

        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const current = document.getElementById('currentPassword').value;
            const nw = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;

            const body = new URLSearchParams({
                action: 'change_password',
                current_password: current,
                new_password: nw,
                confirm_password: confirm
            });

            const submitBtn = passwordForm.querySelector('.btn-account.primary');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang đổi...';
            }

            try {
                const res = await fetch(ACCOUNT_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });
                const data = await res.json();
                notify(data.message || '');

                if (data.success) {
                    passwordForm.reset();
                    closeAccountModal('passwordModal');
                }
            } catch (err) {
                console.error(err);
                notify('Có lỗi xảy ra. Vui lòng thử lại.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Đổi mật khẩu';
                }
            }
        });
    }

    // ---------- ĐĂNG XUẤT ----------
    // ---------- ĐĂNG XUẤT ----------
    const logoutAccountBtn = document.getElementById('logoutAccountBtn'); // nút trong tab Tài khoản
    const navLogoutBtn = document.getElementById('logoutBtn');        // nút góc trên navbar

    function bindLogout(btn) {
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const body = new URLSearchParams({ action: 'logout' });

            try {
                const res = await fetch(ACCOUNT_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });
                const data = await res.json();
                notify(data.message || '');
                if (data.success) {
                    // dùng đường dẫn tuyệt đối cho chắc
                    window.location.href = '/login.php';
                }
            } catch (err) {
                console.error(err);
                notify('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    }

    // Gắn sự kiện cho cả 2 nút
    bindLogout(logoutAccountBtn);
    bindLogout(navLogoutBtn);

    // ---------- XOÁ TÀI KHOẢN ----------
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteForm = document.getElementById('deleteForm');

    if (deleteAccountBtn && deleteForm) {
        deleteAccountBtn.addEventListener('click', () => {
            deleteForm.reset();
            openAccountModal('deleteModal');
        });

        deleteForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!confirm('Bạn chắc chắn muốn xoá tài khoản?')) return;

            const password = document.getElementById('deletePassword').value;

            const body = new URLSearchParams({
                action: 'delete_account',
                password
            });

            const submitBtn = deleteForm.querySelector('.btn-account.danger');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang xoá...';
            }

            try {
                const res = await fetch(ACCOUNT_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });
                const data = await res.json();
                notify(data.message || '');
                if (data.success) {
                    window.location.href = 'index.php?page=consumer';
                }
            } catch (err) {
                console.error(err);
                notify('Có lỗi xảy ra. Vui lòng thử lại.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Tôi hiểu, xoá tài khoản';
                }
            }
        });
    }

    // ---------- LOAD INFO USER (current_user) ĐỂ HIỆN TÊN + EMAIL ----------
    fetch('/index.php?page=current_user', {
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.data) return;
            const user = data.data;

            const nameEl = document.querySelector('.profile-info .profile-name') ||
                document.querySelector('.profile-info h2');
            const emailEl = document.querySelector('.profile-info .profile-email');

            if (nameEl) nameEl.textContent = user.name || 'Người dùng';
            if (emailEl) emailEl.textContent = user.email || '';
        })
        .catch(err => {
            console.error('Lỗi load current_user:', err);
        });
});
if (logoutAccountBtn) {
    logoutAccountBtn.addEventListener('click', async () => {
        const body = new URLSearchParams({ action: 'logout' });

        try {
            const res = await fetch(ACCOUNT_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });
            const data = await res.json();
            notify(data.message || '');

            if (data.success) {
                // 🔥 clear mọi thứ trên frontend
                try {
                    localStorage.removeItem('EV_API_KEY');
                    // nếu còn dùng localStorage cart cũ thì:
                    localStorage.removeItem('cart');
                } catch (e) { }

                window.location.href = 'login.php';
            }
        } catch (err) {
            console.error(err);
            notify('Có lỗi xảy ra. Vui lòng thử lại.');
        }
    });
}
