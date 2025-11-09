// ==================== API CONFIGURATION ====================
const API_BASE = 'http://localhost/dt_provider/backend/api';

// Generic API call function
async function apiCall(endpoint, options = {}) {
    try {
        console.log('📡 Calling API:', `${API_BASE}/${endpoint}`);
        
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status} - ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'API call failed');
        }
        
        console.log('✅ API Success:', data);
        return data;
        
    } catch (error) {
        console.error('❌ API Error:', error.message);
        throw error;
    }
}

// Dashboard API functions
async function loadDashboardDataFromAPI() {
    try {
        const data = await apiCall('dashboard_stats.php');
        return data;
    } catch (error) {
        console.log('🔄 Using fallback sample data');
        return {
            success: true,
            stats: {
                downloads: '1,247',
                revenue: '12,500,000 VND',
                datasets: '8',
                users: '15'
            },
            activities: [
                {
                    type: 'download',
                    description: 'Tải xuống dữ liệu pin - Công ty ABC',
                    amount: '+500,000 VND',
                    icon: 'fa-download',
                    color: '#00d4ff',
                    time: '2 giờ trước'
                }
            ],
            user: {
                company_name: 'EV Data Corp',
                avatar: 'E'
            }
        };
    }
}

// Datasets API functions
async function loadDatasetsFromAPI(search = '') {
    try {
        const data = await apiCall(`datasets.php?search=${encodeURIComponent(search)}`);
        return data;
    } catch (error) {
        console.log('🔄 Using sample datasets data');
        return {
            success: true,
            data: []
        };
    }
}

async function addDatasetToAPI(datasetData) {
    try {
        const data = await apiCall('datasets.php', {
            method: 'POST',
            body: JSON.stringify(datasetData)
        });
        return data;
    } catch (error) {
        console.log('🔄 Using demo mode for add dataset');
        return {
            success: false,
            message: 'Lỗi kết nối API'
        };
    }
}

async function updateDatasetInAPI(id, datasetData) {
    try {
        const data = await apiCall('datasets.php', {
            method: 'PUT', 
            body: JSON.stringify({ id, ...datasetData })
        });
        return data;
    } catch (error) {
        console.log('🔄 Using demo mode for update dataset');
        return {
            success: false,
            message: 'Lỗi kết nối API'
        };
    }
}

async function deleteDatasetFromAPI(id) {
    try {
        const data = await apiCall('datasets.php', {
            method: 'DELETE',
            body: JSON.stringify({ id })
        });
        return data;
    } catch (error) {
        console.log('🔄 Using demo mode for delete dataset');
        return {
            success: false,
            message: 'Lỗi kết nối API'
        };
    }
}

// ==================== DỮ LIỆU MẪU ====================

// Dữ liệu user
let userData = {
    companyName: "EV Data Corp",
    email: "contact@evdatacorp.com",
    contactPerson: "Nguyễn Văn A",
    phone: "+84 123 456 789",
    address: "Hà Nội, Việt Nam",
    description: "Cung cấp dữ liệu EV chất lượng cao cho thị trường"
};

// Dữ liệu chính sách giá
let pricingPolicy = {
    defaultModel: 'per-download',
    defaultPrice: 500000,
    defaultCurrency: 'VND',
    defaultUsageRights: 'commercial',
    defaultLicense: 'Dữ liệu được cung cấp bởi EV Data Corp...'
};

// Biến toàn cục
let currentEditId = null;

// ==================== KHỞI TẠO TRANG ====================

document.addEventListener('DOMContentLoaded', function() {
    // Load user data từ localStorage nếu có
    const savedUserData = localStorage.getItem('userData');
    if (savedUserData) {
        userData = JSON.parse(savedUserData);
    }
    
    // Load pricing policy từ localStorage nếu có
    const savedPricingPolicy = localStorage.getItem('pricingPolicy');
    if (savedPricingPolicy) {
        try {
            pricingPolicy = JSON.parse(savedPricingPolicy);
        } catch (error) {
            console.error('Lỗi load pricing policy:', error);
        }
    }
    
    // Cập nhật hiển thị user trên tất cả các trang
    updateUserDisplay();
    
    const path = window.location.pathname;
    const page = path.split('/').pop().replace('.html', '')
    
    // Cập nhật menu active
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === page + '.html') {
            link.classList.add('active');
        }
    });
    
    // Khởi tạo trang cụ thể
    if (page === 'dashboard') {
        initDashboard();
    } else if (page === 'data') {
        initDataPage();
    } else if (page === 'pricing') {
        initPricingPage();
    } else if (page === 'revenue') {
        initRevenuePage();
    } else if (page === 'privacy') {
        initPrivacyPage();
    } else if (page === 'settings') {
        initSettingsPage();
    }
});

// ==================== DASHBOARD ====================

async function initDashboard() {
    try {
        const data = await loadDashboardDataFromAPI();
        
        if (data.success) {
            // Update stats
            document.getElementById('stat-downloads').textContent = data.stats.downloads;
            document.getElementById('stat-revenue').textContent = data.stats.revenue;
            document.getElementById('stat-datasets').textContent = data.stats.datasets;
            document.getElementById('stat-users').textContent = data.stats.users;
            
            // Update activities
            updateActivities(data.activities);
            
            // Update user info
            if (data.user) {
                const userAvatar = document.getElementById('user-avatar');
                const userName = document.getElementById('user-company-name');
                if (userAvatar) userAvatar.textContent = data.user.avatar;
                if (userName) userName.textContent = data.user.company_name;
            }
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
    
    initUserDropdown();
}

// ==================== TRANG CHÍNH SÁCH GIÁ ====================

function initPricingPage() {
    // Load dữ liệu từ localStorage
    loadPricingPolicy();
    
    // Xử lý form lưu chính sách giá
    const form = document.getElementById('pricing-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            savePricingPolicy();
        };
    }
    
    initUserDropdown();
}

// Load chính sách giá từ localStorage
function loadPricingPolicy() {
    const savedPricingPolicy = localStorage.getItem('pricingPolicy');
    if (savedPricingPolicy) {
        try {
            pricingPolicy = JSON.parse(savedPricingPolicy);
            
            // Cập nhật giá trị lên form
            document.getElementById('pricing-model').value = pricingPolicy.defaultModel;
            document.getElementById('price-value').value = pricingPolicy.defaultPrice;
            document.getElementById('currency').value = pricingPolicy.defaultCurrency;
            
        } catch (error) {
            console.error('Lỗi load pricing policy:', error);
        }
    }
}

// Lưu chính sách giá
function savePricingPolicy() {
    const model = document.getElementById('pricing-model').value;
    const price = parseInt(document.getElementById('price-value').value);
    const currency = document.getElementById('currency').value;
    
    if (!model || !price || price <= 0) {
        alert('Vui lòng điền đầy đủ thông tin hợp lệ');
        return;
    }
    
    // Cập nhật pricing policy
    pricingPolicy = {
        defaultModel: model,
        defaultPrice: price,
        defaultCurrency: currency,
        defaultUsageRights: pricingPolicy.defaultUsageRights,
        defaultLicense: pricingPolicy.defaultLicense
    };
    
    // Lưu vào localStorage
    localStorage.setItem('pricingPolicy', JSON.stringify(pricingPolicy));
    
    alert('✅ Đã lưu chính sách giá thành công!');
    
    // Cập nhật ngay lập tức trên trang thêm dữ liệu nếu đang mở
    applyPricingPolicy();
}

// ==================== TRANG QUẢN LÝ DỮ LIỆU ====================

function initDataPage() {
    loadDataTable();
    initFileUpload();
    initSearch();
    initUserDropdown();
    
    // Áp dụng chính sách giá - GỌI NGAY KHI KHỞI TẠO TRANG
    applyPricingPolicy();
    
    // Xử lý form thêm dữ liệu
    const form = document.getElementById('add-data-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            addDataSource();
        };
    }
}

// Áp dụng chính sách giá lên form thêm dữ liệu
function applyPricingPolicy() {
    console.log('🔄 Applying pricing policy:', pricingPolicy);
    
    const priceInput = document.getElementById('data-price');
    const priceUnitSelect = document.getElementById('price-unit');
    const defaultPriceDisplay = document.getElementById('default-price-display');
    
    if (priceInput && pricingPolicy.defaultPrice) {
        priceInput.value = pricingPolicy.defaultPrice;
        console.log('✅ Set price input to:', pricingPolicy.defaultPrice);
    }
    
    if (priceUnitSelect && pricingPolicy.defaultModel) {
        priceUnitSelect.value = pricingPolicy.defaultModel;
        console.log('✅ Set price unit to:', pricingPolicy.defaultModel);
    }
    
    if (defaultPriceDisplay && pricingPolicy.defaultPrice) {
        defaultPriceDisplay.textContent = formatPrice(pricingPolicy.defaultPrice) + ' ' + (pricingPolicy.defaultCurrency || 'VND');
        console.log('✅ Set default price display to:', formatPrice(pricingPolicy.defaultPrice));
    }
}

// Tải danh sách dữ liệu từ API
async function loadDataTable(search = '') {
    try {
        const response = await loadDatasetsFromAPI(search);
        const datasets = response.data || [];
        
        console.log('📊 Datasets loaded:', datasets);
        
        const tableBody = document.getElementById('data-sources-body');
        if (tableBody) {
            if (datasets.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-light);">
                            <i class="fas fa-database" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            Chưa có dữ liệu nào
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = datasets.map(data => {
                const statusText = getStatusText(data);
                const statusClass = getStatusClass(data);
                
                return `
                <tr>
                    <td>
                        <div style="font-weight: 600;">${data.name || 'Không có tên'}</div>
                        <div style="color: var(--gray-light); font-size: 0.8rem;">
                            ${(data.description || '').substring(0, 50)}...
                        </div>
                    </td>
                    <td>${getDataTypeText(data.data_type)}</td>
                    <td>${getDataFormatText(data.data_format)}</td>
                    <td>
                        <div class="price-badge">${formatPrice(data.price || 0)} VND</div>
                        <div style="color: var(--gray-light); font-size: 0.8rem; margin-top: 0.25rem;">
                            ${getPriceUnitText(data.price_unit)}
                        </div>
                    </td>
                    <td>
                        <span class="${statusClass}">
                            ${statusText}
                        </span>
                        ${data.admin_status === 'rejected' ? `
                            <div style="margin-top: 0.25rem;">
                                <button class="btn-resubmit" onclick="resubmitData(${data.id})" 
                                        style="background: none; border: none; color: var(--accent); font-size: 0.8rem; cursor: pointer;">
                                    <i class="fas fa-redo"></i> Gửi lại
                                </button>
                            </div>
                        ` : ''}
                    </td>
                    <td>${data.download_count || 0}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button class="btn btn-outline" onclick="deleteDataSource(${data.id})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading data table:', error);
        const tableBody = document.getElementById('data-sources-body');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #ef4444;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        Lỗi tải dữ liệu từ server
                    </td>
                </tr>
            `;
        }
    }
}

// Thêm dữ liệu mới
async function addDataSource() {
    const name = document.getElementById('data-name').value;
    const type = document.getElementById('data-type').value;
    const format = document.getElementById('data-format').value;
    const price = parseInt(document.getElementById('data-price').value);
    const priceUnit = document.getElementById('price-unit').value;
    const description = document.getElementById('data-description').value;
    const tags = document.getElementById('data-tags').value;
    
    console.log('📝 Form data:', { name, type, format, price, priceUnit, description, tags });
    
    if (!name || !type || !format || !price) {
        alert('Vui lòng điền đầy đủ thông tin bắt buộc');
        return;
    }

    try {
        const datasetData = {
            name: name,
            data_type: type,
            data_format: format,
            price: price,
            price_unit: priceUnit,
            description: description,
            tags: tags
        };

        const result = await addDatasetToAPI(datasetData);
        
        if (result.success) {
            alert(`✅ ${result.message}`);
            resetForm();
            await loadDataTable();
        } else {
            alert(`❌ ${result.message}`);
        }
    } catch (error) {
        alert('❌ Lỗi khi thêm dữ liệu: ' + error.message);
    }
}

// Gửi lại dữ liệu bị từ chối
async function resubmitData(id) {
    if (confirm('Bạn có chắc muốn gửi lại bộ dữ liệu này để duyệt?')) {
        try {
            const result = await updateDatasetInAPI(id, { admin_status: 'pending' });
            if (result.success) {
                alert('✅ ' + result.message);
                await loadDataTable();
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            alert('❌ Lỗi khi gửi lại dữ liệu: ' + error.message);
        }
    }
}

// Xóa dữ liệu
async function deleteDataSource(id) {
    if (confirm('Bạn có chắc muốn xóa bộ dữ liệu này?')) {
        try {
            const result = await deleteDatasetFromAPI(id);
            if (result.success) {
                alert('✅ ' + result.message);
                await loadDataTable();
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            alert('❌ Lỗi khi xóa dữ liệu: ' + error.message);
        }
    }
}

// ==================== HÀM HỖ TRỢ ====================

function resetForm() {
    document.getElementById('add-data-form').reset();
    removeFile();
    // Áp dụng lại chính sách giá sau khi reset
    applyPricingPolicy();
}

function initFileUpload() {
    const uploadArea = document.getElementById('file-upload-area');
    const fileInput = document.getElementById('data-file');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) handleFileSelect(files[0]);
        });
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) handleFileSelect(e.target.files[0]);
        });
    }
}

function handleFileSelect(file) {
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    
    if (file) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        fileName.textContent = file.name;
        fileSize.textContent = `${fileSizeMB} MB`;
        fileInfo.style.display = 'block';
    }
}

function removeFile() {
    const fileInput = document.getElementById('data-file');
    const fileInfo = document.getElementById('file-info');
    fileInput.value = '';
    fileInfo.style.display = 'none';
}

function initSearch() {
    const searchInput = document.getElementById('search-data');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            loadDataTable(e.target.value);
        });
    }
}

function refreshData() {
    loadDataTable();
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

function getPriceUnitText(unit) {
    const units = {
        'per-download': '/lượt tải',
        'subscription': '/tháng',
        'one-time': 'một lần'
    };
    return units[unit] || unit;
}

function getDataTypeText(type) {
    const types = {
        'battery': 'Dữ liệu pin',
        'driving': 'Hành vi lái xe',
        'charging': 'Trạm sạc',
        'v2g': 'Giao dịch V2G'
    };
    return types[type] || type;
}

function getDataFormatText(format) {
    return format === 'raw' ? 'Dữ liệu thô' : 'Đã phân tích';
}

// Hàm trạng thái đơn giản theo yêu cầu
function getStatusText(dataset) {
    const adminStatus = dataset.admin_status;
    
    if (adminStatus === 'rejected') return 'Bị từ chối';
    if (adminStatus === 'pending' || !adminStatus) return 'Chờ duyệt';
    if (adminStatus === 'approved') return 'Đã duyệt';
    
    return 'Không xác định';
}

function getStatusClass(dataset) {
    const adminStatus = dataset.admin_status;
    
    if (adminStatus === 'rejected') return 'status-rejected';
    if (adminStatus === 'pending' || !adminStatus) return 'status-pending';
    if (adminStatus === 'approved') return 'status-active';
    
    return 'status-pending';
}

function updateActivities(activities) {
    const activityList = document.querySelector('.activity-list');
    if (!activityList) return;
    
    activityList.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <i class="fas ${activity.icon}" style="color: ${activity.color};"></i>
            <div>
                <div style="font-weight: 600;">${activity.description}</div>
                <div style="color: #94a3b8; font-size: 0.9rem;">${activity.time}</div>
            </div>
            <div style="color: ${activity.color}; font-weight: 600;">${activity.amount}</div>
        </div>
    `).join('');
}

function initUserDropdown() {
    const userInfo = document.getElementById('user-info-dropdown');
    const dropdown = document.getElementById('user-dropdown');
    
    if (userInfo && dropdown) {
        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', function() {
            dropdown.classList.remove('show');
        });
    }
}

function updateUserDisplay() {
    const companyNameElements = document.querySelectorAll('#user-company-name');
    const avatarElements = document.querySelectorAll('#user-avatar');
    
    companyNameElements.forEach(element => {
        element.textContent = userData.companyName;
    });
    avatarElements.forEach(element => {
        element.textContent = userData.companyName.charAt(0);
    });
    localStorage.setItem('userData', JSON.stringify(userData));
}

// ==================== CÁC TRANG KHÁC ====================

function initRevenuePage() {
    initUserDropdown();
}

function initPrivacyPage() {
    initUserDropdown();
}

function initSettingsPage() {
    loadUserData();
    initUserDropdown();
}

function loadUserData() {
    document.getElementById('company-name').value = userData.companyName;
    document.getElementById('company-email').value = userData.email;
    document.getElementById('company-phone').value = userData.phone;
    document.getElementById('contact-person').value = userData.contactPerson;
    document.getElementById('company-address').value = userData.address;
    document.getElementById('company-description').value = userData.description;
    updateUserDisplay();
}

// Thêm CSS cho trạng thái
const style = document.createElement('style');
style.textContent = `
    .status-active {
        background: #dcfce7;
        color: #166534;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-rejected {
        background: #fee2e2;
        color: #dc2626;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .price-badge {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
`;
document.head.appendChild(style);

// Utility functions
function showProfile() {
    window.location.href = 'settings.html';
}

function showSettings() {
    window.location.href = 'settings.html';
}

function logout() {
    if (confirm('Bạn có chắc muốn đăng xuất?')) {
        localStorage.removeItem('userData');
        localStorage.removeItem('pricingPolicy');
        window.location.href = 'login.html';
    }
}