const USER_ID = 4;
const API_BASE = "http://localhost/EV-Data-Analytics-Marketplace/backend/data-consumer-service/index.php?page=datasets";
let packagesData = [];
let cart = [];
let currentFilters = { category: '', price: 1000000000, format: '', vehicleType: '' };
let currentSort = 'popular';

// ===========================
// Format tiền VNĐ
// ===========================
function formatVND(value) {
    return value.toLocaleString('vi-VN') + ' VNĐ';
}

// ===========================
// Load trang
// ===========================
document.addEventListener('DOMContentLoaded', async () => {
    initializeEventListeners();

    // Load cart từ localStorage
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
    }

    await loadRealPackages();

    // Nếu user login, load cart từ server
    if (typeof USER_LOGGED_IN !== 'undefined' && USER_LOGGED_IN) {
        await loadCartFromServer();
    }
});

// ===========================
// Load gói dữ liệu
// ===========================
async function loadRealPackages() {
    try {
        const response = await fetch(API_BASE);
        const result = await response.json();
        if (result.success && Array.isArray(result.data)) {
            packagesData = result.data.map(item => ({
                id: parseInt(item.id),
                title: item.name,
                description: `Dữ liệu ${item.type} tại khu vực ${item.region}`,
                icon: getIcon(item.type),
                price: parseFloat(item.price),
                rent_month: parseFloat(item.rent_monthly || 0),
                rent_year: parseFloat(item.rent_yearly || 0),
                rating: parseFloat((Math.random() * 1.5 + 3.5).toFixed(1)),
                reviews: Math.floor(Math.random() * 200 + 50),
                category: item.type,
                format: 'csv',
                updated: new Date().toISOString().split('T')[0],
                vehicleType: item.vehicleType || ''
            }));
            renderPackages(sortPackages(packagesData));
        } else renderPackages([]);
    } catch (err) {
        console.error(err);
        renderPackages([]);
    }
}

// ===========================
// Icon
// ===========================
function getIcon(type) {
    switch (type) {
        case "battery": return "🔋";
        case "driver": return "🚗";
        case "charging": return "⚡";
        default: return "📊";
    }
}

// ===========================
// Event
// ===========================
function initializeEventListeners() {
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.addEventListener('click', e => switchPage(e.currentTarget.dataset.page));
    });
    document.getElementById('filterBtn')?.addEventListener('click', () => {
        const panel = document.getElementById('filterPanel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });
    document.getElementById('applyFilterBtn')?.addEventListener('click', applyFilters);
    document.getElementById('sortSelect')?.addEventListener('change', handleSortChange);
    document.getElementById('searchInput')?.addEventListener('input', handleSearch);
    document.getElementById('cartBtn')?.addEventListener('click', () => document.getElementById('cartModal').classList.add('active'));
    document.getElementById('closeCartBtn')?.addEventListener('click', () => document.getElementById('cartModal').classList.remove('active'));
    document.getElementById('closeDetailBtn')?.addEventListener('click', () => document.getElementById('detailModal').classList.remove('active'));

    const priceSlider = document.getElementById('priceFilter');
    const priceValue = document.getElementById('priceValue');
    if (priceSlider && priceValue) {
        priceSlider.addEventListener('input', () => {
            const val = Number(priceSlider.value);
            priceValue.textContent = val.toLocaleString('vi-VN');
            currentFilters.price = val;
            renderPackages(sortPackages(filterPackages(packagesData)));
        });
    }

    const checkoutBtn = document.querySelector(".checkout-btn");
    if (checkoutBtn) checkoutBtn.addEventListener("click", checkoutCart);
}

// ===========================
// Bộ lọc & sắp xếp
// ===========================
function handleSortChange(e) { currentSort = e.target.value; renderPackages(sortPackages(filterPackages(packagesData))); }
function handleSearch(e) {
    const query = e.target.value.toLowerCase();
    renderPackages(filterPackages(packagesData).filter(pkg =>
        pkg.title.toLowerCase().includes(query) || pkg.description.toLowerCase().includes(query)
    ));
}
function applyFilters() {
    currentFilters.category = document.getElementById('categoryFilter')?.value || '';
    currentFilters.price = parseFloat(document.getElementById('priceFilter')?.value || 1000000000);
    currentFilters.format = document.getElementById('formatFilter')?.value || '';
    currentFilters.vehicleType = document.getElementById('vehicleTypeFilter')?.value || '';
    renderPackages(sortPackages(filterPackages(packagesData)));
    showToast("🎯 Bộ lọc đã được áp dụng");
}
function filterPackages(packages) {
    return packages.filter(pkg =>
        (!currentFilters.category || pkg.category === currentFilters.category) &&
        pkg.price <= currentFilters.price &&
        (!currentFilters.format || pkg.format === currentFilters.format) &&
        (!currentFilters.vehicleType || pkg.vehicleType === currentFilters.vehicleType)
    );
}
function sortPackages(packages) {
    const sorted = [...packages];
    switch (currentSort) {
        case "newest": sorted.sort((a, b) => new Date(b.updated) - new Date(a.updated)); break;
        case "price-low": sorted.sort((a, b) => a.price - b.price); break;
        case "price-high": sorted.sort((a, b) => b.price - a.price); break;
        case "rating": sorted.sort((a, b) => b.rating - b.rating); break;
        default: sorted.sort((a, b) => b.reviews - a.reviews); break;
    }
    return sorted;
}

// ===========================
// Render Packages
// ===========================
function renderPackages(packages) {
    const grid = document.getElementById('packagesGrid');
    if (!grid) return;
    if (!packages.length) { grid.innerHTML = "<p style='color:gray;'>Không có dữ liệu nào.</p>"; return; }

    grid.innerHTML = packages.map(pkg => `
        <div class="package-card">
            <div class="package-icon">${pkg.icon}</div>
            <div class="package-title">${pkg.title}</div>
            <div class="package-description">${pkg.description}</div>
            <div class="package-meta">
                <div class="package-rating">⭐ ${pkg.rating}</div>
                <div>${pkg.reviews} reviews</div>
            </div>
            <div class="package-price">Mua: ${formatVND(pkg.price)}</div>
            <div class="package-buttons">
                <button class="btn btn-primary" onclick="viewDetails(${pkg.id})">Chi tiết</button>
                <button class="btn btn-secondary" onclick="addToCartQuick(${pkg.id})">Thêm vào giỏ</button>
            </div>
        </div>
    `).join('');
}

// ===========================
// Thêm vào giỏ với quantity
// ===========================
function addToCartItem(pkg, type, price) {
    const exist = cart.find(c => c.id === pkg.id && c.selectedType === type);
    if (exist) {
        exist.quantity = (exist.quantity || 1) + 1;
        syncCartItemBackend(pkg.id, type, exist.quantity);
    } else {
        const item = {
            ...pkg,
            cartId: Date.now() + Math.random(),
            selectedType: type,
            price,
            quantity: 1,
            selected: true
        };
        cart.push(item);
        syncCartItemBackend(pkg.id, type, 1);
    }
    updateCartUI();
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Thêm nhanh từ card
function addToCartQuick(id) {
    const pkg = packagesData.find(p => p.id === id);
    if (!pkg) return;
    addToCartItem(pkg, 'Mua', pkg.price);
    showToast(`🛒 ${pkg.title} đã được thêm vào giỏ`);
}

// Thêm từ chi tiết
async function viewDetails(id) {
    const pkg = packagesData.find(p => p.id === id);
    if (!pkg) { showToast("Không tìm thấy gói dữ liệu."); return; }
    document.getElementById('detailTitle').textContent = pkg.title;
    document.getElementById('detailContent').innerHTML = `
        <p><strong>Mã gói:</strong> ${pkg.id}</p>
        <p><strong>Loại dữ liệu:</strong> ${pkg.category}</p>
        <p><strong>Khu vực:</strong> ${pkg.description}</p>
        <p><strong>Mua:</strong> <input type="checkbox" id="buyCheckbox" checked data-price="${pkg.price}"> ${formatVND(pkg.price)}</p>
        <p><strong>Thuê (tháng):</strong> <input type="checkbox" id="rentMonthCheckbox" checked data-price="${pkg.rent_month}"> ${formatVND(pkg.rent_month)}</p>
        <p><strong>Thuê (năm):</strong> <input type="checkbox" id="rentYearCheckbox" checked data-price="${pkg.rent_year}"> ${formatVND(pkg.rent_year)}</p>
        <button class="btn btn-primary" id="addDetailCartBtn">Thêm vào giỏ</button>
    `;
    document.getElementById('detailModal').classList.add('active');

    document.getElementById('addDetailCartBtn').onclick = () => {
        const selections = [];
        const buyCB = document.getElementById('buyCheckbox');
        const monthCB = document.getElementById('rentMonthCheckbox');
        const yearCB = document.getElementById('rentYearCheckbox');
        if (buyCB.checked) selections.push({ type: 'Mua', price: parseFloat(buyCB.dataset.price) });
        if (monthCB.checked) selections.push({ type: 'Thuê tháng', price: parseFloat(monthCB.dataset.price) });
        if (yearCB.checked) selections.push({ type: 'Thuê năm', price: parseFloat(yearCB.dataset.price) });
        if (!selections.length) { showToast("Chọn ít nhất 1 phương thức"); return; }
        selections.forEach(sel => addToCartItem(pkg, sel.type, sel.price));
        showToast("🛒 Đã thêm vào giỏ");
        document.getElementById('detailModal').classList.remove('active');
    }
    document.getElementById('detailModal').classList.add('active');

    // Gọi để kiểm tra purchase và hiển thị nút Tải xuống
    handleModalDetail(pkg.id);

}

// ===========================
// Đồng bộ backend
// ===========================
function syncCartItemBackend(package_id, selected_type, quantity) {
    fetch("/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/cart.php", {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ package_id, selected_type, quantity })
    });
}

// ===========================
// Load cart từ server
// ===========================
async function loadCartFromServer() {
    try {
        const res = await fetch('/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/cart.php?action=get', { credentials: 'include' });
        const data = await res.json();
        if (data.success && Array.isArray(data.cart)) {
            cart = data.cart.map(item => ({
                cartId: item.id,
                id: item.package_id,
                selectedType: item.selected_type,
                price: parseFloat(item.price),
                quantity: parseInt(item.quantity),
                selected: true,
                title: packagesData.find(p => p.id == item.package_id)?.title || 'Gói dữ liệu',
                category: packagesData.find(p => p.id == item.package_id)?.category || ''
            }));
            updateCartUI();
            localStorage.setItem('cart', JSON.stringify(cart));
        }
    } catch (err) { console.error(err); }
}

// ===========================
// Cập nhật UI giỏ hàng
// ===========================
function updateCartUI() {
    const cartCount = document.getElementById("cartCount");
    const cartItems = document.getElementById("cartItems");
    const cartTotalEl = document.getElementById("cartTotal");

    cartCount.textContent = cart.reduce((sum, i) => sum + (i.selected ? i.quantity : 0), 0);

    if (cart.length === 0) { cartItems.innerHTML = "<p class='empty-cart'>Giỏ hàng trống</p>"; cartTotalEl.textContent = "0 VNĐ"; return; }

    cartItems.innerHTML = cart.map(item => `
        <div class="cart-item">
            <input type="checkbox" class="cart-select-checkbox" data-cartid="${item.cartId}" ${item.selected ? 'checked' : ''}>
            <div class="cart-item-info">
                <h4>${item.title} (${item.selectedType}) x ${item.quantity}</h4>
                <p>${item.category}</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="cart-item-price">${formatVND(item.price * item.quantity)}</span>
                <button class="remove-btn" onclick="removeFromCart(${item.cartId})">Xóa</button>
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.cart-select-checkbox').forEach(chk => {
        chk.addEventListener('change', e => {
            const cartId = parseFloat(e.target.dataset.cartid);
            const cartItem = cart.find(c => c.cartId === cartId);
            if (cartItem) cartItem.selected = e.target.checked;
            updateCartTotal();
        });
    });

    updateCartTotal();
}
function updateCartTotal() {
    const cartTotalEl = document.getElementById("cartTotal");
    const total = cart.filter(i => i.selected).reduce((sum, i) => sum + i.price * i.quantity, 0);
    cartTotalEl.textContent = formatVND(total);
}

// ===========================
// Xóa item
// ===========================
function removeFromCart(cartId) {
    const item = cart.find(i => i.cartId === cartId);
    if (item) {
        fetch("/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/cart.php?action=remove", {
            method: "POST",
            credentials: 'include',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ package_id: item.id, selected_type: item.selectedType })
        });
    }
    cart = cart.filter(i => i.cartId !== cartId);
    updateCartUI();
    localStorage.setItem('cart', JSON.stringify(cart));
    showToast("❌ Đã xóa khỏi giỏ hàng");
}

// ===========================
// Thanh toán qua PayOS (1 dataset / lần thanh toán)
// ===========================
// ===========================
// Thanh toán qua PayOS cho nhiều item trong giỏ
// ===========================
async function checkoutCart() {
    console.log(">>> checkoutCart CLICKED");

    if (typeof cart === "undefined") {
        alert("Biến cart chưa được khai báo.");
        return;
    }

    const selectedItems = cart.filter(i => i.selected);
    console.log("selectedItems:", selectedItems);

    if (!selectedItems.length) {
        alert("Giỏ hàng trống hoặc chưa chọn item nào để thanh toán.");
        return;
    }

    const userId = typeof USER_ID !== "undefined" ? USER_ID : null;
    console.log("USER_ID =", userId);

    if (!userId) {
        alert("Không xác định được user. Hãy chắc chắn đã set USER_ID.");
        return;
    }

    const items = selectedItems.map(i => ({
        dataset_id: i.id,
        type: i.selectedType,   // "Mua" / "Thuê tháng" / Thuê năm
        price: i.price,
        quantity: i.quantity || 1
    }));
    const totalAmount = items.reduce(
        (sum, it) => sum + it.price * it.quantity,
        0
    );

    console.log("items gửi lên:", items);
    console.log("totalAmount gửi lên:", totalAmount);

    try {
        const res = await fetch(
            "/EV-Data-Analytics-Marketplace/backend/data-consumer-service/payment/create_payment.php",
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    user_id: userId,
                    items: items,
                    totalAmount: totalAmount
                })
            }
        );

        console.log("res.status =", res.status);
        const data = await res.json();
        console.log("create_payment response:", data);

        if (!data.success) {
            alert(data.message || "Không tạo được link thanh toán PayOS.");
            return;
        }

        const checkoutUrl =
            data.checkout_url ||
            (data.payos_raw && data.payos_raw.checkoutUrl);

        console.log("checkoutUrl resolved:", checkoutUrl);

        if (!checkoutUrl) {
            alert("Không tìm thấy checkout_url trong response.");
            return;
        }

        // Không alert nữa, đi thẳng qua PayOS
        // Mở tab mới:
        window.open(checkoutUrl, "_blank");

        // Hoặc nếu bạn muốn chuyển ngay tab hiện tại thì dùng:
        // window.location.href = checkoutUrl;

    } catch (err) {
        console.error("checkoutCart error:", err);
        alert("Lỗi kết nối tới server khi tạo thanh toán PayOS.");
    }
}



// ===========================
// Toast
// ===========================
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2500);
}

// ===========================
// Chuyển trang
// ===========================
function switchPage(page) {
    document.querySelectorAll('.page').forEach(p => p.style.display = 'none');
    const targetPage = document.getElementById(`${page}-page`);
    if (targetPage) targetPage.style.display = 'block';
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.page === page);
    });
}
async function accessDataset(datasetId) {
    try {
        const res = await fetch(`/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/data_access.php?dataset_id=${datasetId}`, {
            credentials: 'include'
        });
        const data = await res.json();
        if (data.success) {
            console.log("Dữ liệu từ API bên thứ 3:", data);
            showToast("✅ Dữ liệu đã sẵn sàng");
        } else {
            showToast("❌ " + data.message);
        }
    } catch (err) {
        console.error(err);
        showToast("❌ Lỗi khi truy cập dữ liệu");
    }
} let aiChartInstance;

function initAIChat() {
    const chatBtn = document.getElementById('ai-chat-btn');
    const chatPopup = document.getElementById('ai-chat-popup');
    const closeBtn = document.getElementById('ai-close-btn');
    const inputEl = document.getElementById('aiUserInput');
    const sendBtn = document.getElementById('aiSendBtn');
    const messagesEl = document.getElementById('ai-messages');

    chatBtn.addEventListener('click', () => {
        chatPopup.style.display = chatPopup.style.display === 'flex' ? 'none' : 'flex';
    });
    closeBtn.addEventListener('click', () => chatPopup.style.display = 'none');
    sendBtn.addEventListener('click', sendAIMessage);
    inputEl.addEventListener('keypress', e => { if (e.key === 'Enter') sendAIMessage(); });

    function addMessage(sender, text, type = 'ai') {
        const p = document.createElement('div');
        p.className = `msg ${type}`;
        p.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messagesEl.appendChild(p);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function sendAIMessage() {
        const msg = inputEl.value.trim();
        if (!msg) return;
        addMessage('Bạn', msg, 'user');
        inputEl.value = '';

        const loadingEl = document.createElement('div');
        loadingEl.className = 'msg ai';
        loadingEl.innerHTML = '<em>Đang trả lời...</em>';
        messagesEl.appendChild(loadingEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const res = await fetch('/EV-Data-Analytics-Marketplace/backend/data-consumer-service/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg, user_id: USER_ID })
            });

            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error', e, text);
                loadingEl.innerHTML = '❌ Lỗi response server';
                return;
            }

            messagesEl.removeChild(loadingEl);
            addMessage('AI', data.reply?.text || 'AI chưa trả lời được');

            if (data.reply?.chartData && Object.keys(data.reply.chartData).length) {
                renderChart(data.reply.chartData.labels, data.reply.chartData.datasets);
            }

            if (data.reply?.alerts) {
                data.reply.alerts.forEach(a => addMessage('⚠️ Cảnh báo', a, 'alert'));
            }

        } catch (err) {
            console.error(err);
            messagesEl.removeChild(loadingEl);
            addMessage('AI', '❌ Lỗi kết nối server');
        }
    }

    function renderChart(labels, datasets) {
        const ctx = document.getElementById('aiChart').getContext('2d');
        if (aiChartInstance) aiChartInstance.destroy();
        aiChartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('analytics-page')) initAIChat();
});