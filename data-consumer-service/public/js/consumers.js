// Gọi datasets qua router backend (Docker)
const API_BASE = "/index.php?page=datasets";

let packagesData = [];
let cart = [];
let currentFilters = {
    category: '',
    price: 1000000000,
    format: '',
    vehicleType: ''
};
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

    // ❌ BỎ load cart từ localStorage
    // const savedCart = localStorage.getItem('cart');

    await loadRealPackages();

    // Luôn thử load cart từ server (nếu chưa login sẽ 401 → xử lý trong hàm)
    await loadCartFromServer();
});

// Load gói dữ liệu (từ backend consumer ↔ DB provider)
// ===========================
async function loadRealPackages() {
    try {
        const response = await fetch(API_BASE);
        const result = await response.json();

        if (result.success && Array.isArray(result.data)) {
            packagesData = result.data.map(item => {
                const unit = item.price_unit || 'per-download';
                const basePrice = parseFloat(item.price || 0);

                let priceBuy = 0;
                let rentMonth = 0;
                let rentYear = 0;

                if (unit === 'per-download') {
                    priceBuy = basePrice;
                } else if (unit === 'subscription') {
                    rentMonth = basePrice;
                } else if (unit === 'one-time') {
                    rentYear = basePrice;
                }

                return {
                    id: parseInt(item.id),
                    title: item.name,
                    description: item.description ? item.description : `Dữ liệu ${item.type}`,
                    icon: getIcon(item.type),
                    price: priceBuy,
                    rent_month: rentMonth,
                    rent_year: rentYear,
                    rating: parseFloat((Math.random() * 1.5 + 3.5).toFixed(1)),
                    reviews: Math.floor(Math.random() * 200 + 50),
                    category: item.type,
                    format: item.format || 'csv',
                    updated: item.created_at ? item.created_at.split(' ')[0] : new Date().toISOString().split('T')[0],
                    vehicleType: item.vehicleType || '',
                    price_unit: unit,
                    hasBuy: priceBuy > 0,
                    hasRentMonth: rentMonth > 0,
                    hasRentYear: rentYear > 0
                };
            });

            renderPackages(sortPackages(packagesData));
        } else {
            renderPackages([]);
        }
    } catch (err) {
        console.error("loadRealPackages error:", err);
        renderPackages([]);
    }
}

function getMainPriceText(pkg) {
    // Ưu tiên theo thứ tự: Mua → Thuê tháng → Thuê năm
    if (pkg.hasBuy) {
        return `Mua: ${formatVND(pkg.price)}`;
    }
    if (pkg.hasRentMonth) {
        return `Thuê tháng: ${formatVND(pkg.rent_month)}`;
    }
    if (pkg.hasRentYear) {
        return `Thuê năm: ${formatVND(pkg.rent_year)}`;
    }
    // Nếu không có giá nào > 0
    return "Giá: Liên hệ";
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
    document.getElementById('cartBtn')?.addEventListener('click', () =>
        document.getElementById('cartModal').classList.add('active')
    );
    document.getElementById('closeCartBtn')?.addEventListener('click', () =>
        document.getElementById('cartModal').classList.remove('active')
    );
    document.getElementById('closeDetailBtn')?.addEventListener('click', () =>
        document.getElementById('detailModal').classList.remove('active')
    );

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
function handleSortChange(e) {
    currentSort = e.target.value;
    renderPackages(sortPackages(filterPackages(packagesData)));
}

function handleSearch(e) {
    const query = e.target.value.toLowerCase();
    renderPackages(
        filterPackages(packagesData).filter(pkg =>
            pkg.title.toLowerCase().includes(query) ||
            pkg.description.toLowerCase().includes(query)
        )
    );
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
        case "rating": sorted.sort((a, b) => b.rating - a.rating); break;
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

    if (!packages.length) {
        grid.innerHTML = "<p style='color:gray;'>Không có dữ liệu nào.</p>";
        return;
    }

    grid.innerHTML = packages.map(pkg => `
    <div class="package-card">
        <div class="package-icon">${pkg.icon}</div>
        <div class="package-title">${pkg.title}</div>
        <div class="package-description">${pkg.description}</div>
        <div class="package-meta">
            <div class="package-rating">⭐ ${pkg.rating}</div>
            <div>${pkg.reviews} reviews</div>
        </div>
        <div class="package-price">${getMainPriceText(pkg)}</div>
        <div class="package-buttons">
            <button class="btn btn-primary" onclick="viewDetails(${pkg.id})">Chi tiết</button>
            <button class="btn btn-secondary" onclick="addToCartQuick(${pkg.id})">Thêm vào giỏ</button>
        </div>
    </div>
`).join('');


}

// ===========================
// Giỏ hàng
// ===========================
function addToCartItem(pkg, type, price) {
    const exist = cart.find(c => c.id === pkg.id && c.selectedType === type);

    if (exist) {
        exist.quantity = (exist.quantity || 1) + 1;
        // sync về backend với quantity mới
        syncCartItemBackend(pkg.id, type, exist.quantity, price);
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
        // sync về backend
        syncCartItemBackend(pkg.id, type, 1, price);
    }

    updateCartUI();
}

// Thêm nhanh từ card
function addToCartQuick(id) {
    const pkg = packagesData.find(p => p.id === id);
    if (!pkg) return;

    let typeLabel = null;
    let price = 0;

    if (pkg.hasBuy) {
        typeLabel = 'Mua';
        price = pkg.price;
    } else if (pkg.hasRentMonth) {
        typeLabel = 'Thuê tháng';
        price = pkg.rent_month;
    } else if (pkg.hasRentYear) {
        typeLabel = 'Thuê năm';
        price = pkg.rent_year;
    } else {
        showToast("Gói này chưa có giá, không thể thêm vào giỏ.");
        return;
    }

    addToCartItem(pkg, typeLabel, price);
    showToast(`🛒 ${pkg.title} (${typeLabel}) đã được thêm vào giỏ`);
}

async function viewDetails(id) {
    const pkg = packagesData.find(p => p.id === id);
    if (!pkg) { showToast("Không tìm thấy gói dữ liệu."); return; }

    document.getElementById('detailTitle').textContent = pkg.title;

    // build phần mô tả chung trước
    let html = `
        <p><strong>Mã gói:</strong> ${pkg.id}</p>
        <p><strong>Loại dữ liệu:</strong> ${pkg.category}</p>
        <p><strong>Mô tả:</strong> ${pkg.description}</p>
    `;

    // build phần lựa chọn mua/thuê theo flag
    html += `<hr><p><strong>Hình thức sử dụng:</strong></p>`;

    if (pkg.hasBuy) {
        html += `
        <p><strong>Mua vĩnh viễn:</strong>
           <input type="checkbox" id="buyCheckbox" checked data-price="${pkg.price}">
           ${formatVND(pkg.price)}
        </p>`;
    }

    if (pkg.hasRentMonth) {
        html += `
        <p><strong>Thuê (tháng):</strong>
           <input type="checkbox" id="rentMonthCheckbox" ${pkg.hasBuy ? '' : 'checked'}
                  data-price="${pkg.rent_month}">
           ${formatVND(pkg.rent_month)}
        </p>`;
    }

    if (pkg.hasRentYear) {
        html += `
        <p><strong>Thuê (năm):</strong>
           <input type="checkbox" id="rentYearCheckbox" ${(!pkg.hasBuy && !pkg.hasRentMonth) ? 'checked' : ''}
                  data-price="${pkg.rent_year}">
           ${formatVND(pkg.rent_year)}
        </p>`;
    }

    // Nếu provider không set cái nào > 0
    if (!pkg.hasBuy && !pkg.hasRentMonth && !pkg.hasRentYear) {
        html += `<p><em>Gói này chưa cấu hình giá, vui lòng liên hệ admin.</em></p>`;
    } else {
        html += `<button class="btn btn-primary" id="addDetailCartBtn">Thêm vào giỏ</button>`;
    }

    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('active');

    // Gắn sự kiện cho nút "Thêm vào giỏ"
    const addBtn = document.getElementById('addDetailCartBtn');
    if (addBtn) {
        addBtn.onclick = () => {
            const selections = [];
            const buyCB = document.getElementById('buyCheckbox');
            const monthCB = document.getElementById('rentMonthCheckbox');
            const yearCB = document.getElementById('rentYearCheckbox');

            if (buyCB && buyCB.checked) selections.push({ type: 'Mua', price: parseFloat(buyCB.dataset.price) });
            if (monthCB && monthCB.checked) selections.push({ type: 'Thuê tháng', price: parseFloat(monthCB.dataset.price) });
            if (yearCB && yearCB.checked) selections.push({ type: 'Thuê năm', price: parseFloat(yearCB.dataset.price) });

            if (!selections.length) { showToast("Chọn ít nhất 1 phương thức"); return; }

            selections.forEach(sel => addToCartItem(pkg, sel.type, sel.price));
            showToast("🛒 Đã thêm vào giỏ");
            document.getElementById('detailModal').classList.remove('active');
        };
    }

    // Gọi để load info purchase + nút tải xuống nếu đã mua
    if (typeof handleModalDetail === 'function') {
        handleModalDetail(pkg.id);
    }
}


// Đồng bộ giỏ hàng với backend (Docker) – dùng POST mặc định (action rỗng)
function syncCartItemBackend(package_id, selected_type, quantity, price) {
    fetch("/api/cart.php", {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ package_id, selected_type, quantity, price })
    }).catch(err => console.error("syncCartItemBackend error:", err));
}

// Load cart từ server (theo user_id trong session)
async function loadCartFromServer() {
    try {
        const res = await fetch("/api/cart.php?action=get", {
            credentials: 'include'
        });

        // Nếu chưa login → server trả 401, đọc text cho chắc
        if (res.status === 401) {
            cart = [];
            updateCartUI();
            return;
        }

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
        } else {
            cart = [];
            updateCartUI();
        }
    } catch (err) {
        console.error("loadCartFromServer error:", err);
    }
}

// Cập nhật UI giỏ hàng
function updateCartUI() {
    const cartCount = document.getElementById("cartCount");
    const cartItems = document.getElementById("cartItems");
    const cartTotalEl = document.getElementById("cartTotal");

    cartCount.textContent = cart.reduce(
        (sum, i) => sum + (i.selected ? i.quantity : 0), 0
    );

    if (!cart.length) {
        cartItems.innerHTML = "<p class='empty-cart'>Giỏ hàng trống</p>";
        cartTotalEl.textContent = "0 VNĐ";
        return;
    }

    cartItems.innerHTML = cart.map(item => `
        <div class="cart-item">
            <input type="checkbox" class="cart-select-checkbox"
                   data-cartid="${item.cartId}" ${item.selected ? 'checked' : ''}>
            <div class="cart-item-info">
                <h4>${item.title} (${item.selectedType}) x ${item.quantity}</h4>
                <p>${item.category}</p>
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="cart-item-price">
                    ${formatVND(item.price * item.quantity)}
                </span>
                <button class="remove-btn" onclick="removeFromCart(${item.cartId}, ${item.id}, '${item.selectedType}')">Xóa</button>
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
    const total = cart
        .filter(i => i.selected)
        .reduce((sum, i) => sum + i.price * i.quantity, 0);

    cartTotalEl.textContent = formatVND(total);
}

// Xóa item khỏi giỏ
function removeFromCart(cartId, packageId, selectedType) {
    // Xoá trên server
    fetch("/api/cart.php?action=remove", {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            package_id: packageId,
            selected_type: selectedType
        })
    }).catch(err => console.error("removeFromCart error:", err));

    // Xoá trong memory
    cart = cart.filter(i => i.cartId !== cartId);
    updateCartUI();
    showToast("❌ Đã xóa khỏi giỏ hàng");
}

// ===========================
// Thanh toán qua PayOS
// ===========================
async function checkoutCart() {
    console.log(">>> checkoutCart CLICKED");

    const selectedItems = cart.filter(i => i.selected);
    if (!selectedItems.length) {
        alert("Giỏ hàng trống hoặc chưa chọn item nào để thanh toán.");
        return;
    }

    const items = selectedItems.map(i => ({
        dataset_id: i.id,
        type: i.selectedType,
        price: i.price,
        quantity: i.quantity || 1
    }));

    const totalAmount = items.reduce(
        (sum, it) => sum + it.price * it.quantity,
        0
    );

    try {
        const res = await fetch(
            "/index.php?page=payment&action=create",
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ items, totalAmount })
            }
        );

        const data = await res.json();
        if (!data.success) {
            alert(data.message || "Không tạo được link thanh toán PayOS.");
            return;
        }

        const checkoutUrl =
            data.checkout_url ||
            (data.payos_raw && data.payos_raw.checkoutUrl);

        if (!checkoutUrl) {
            alert("Không tìm thấy checkout_url trong response.");
            return;
        }

        window.open(checkoutUrl, "_blank");
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
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2500);
}

// ===========================
// Chuyển page
// ===========================
function switchPage(page) {
    document.querySelectorAll('.page').forEach(p => p.style.display = 'none');
    const targetPage = document.getElementById(`${page}-page`);
    if (targetPage) targetPage.style.display = 'block';

    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.page === page);
    });
}

// ===========================
// Truy cập dataset (3rd-party API)
// ===========================
async function accessDataset(datasetId) {
    try {
        const res = await fetch(`/api/data_access.php?dataset_id=${datasetId}`, {
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
}
