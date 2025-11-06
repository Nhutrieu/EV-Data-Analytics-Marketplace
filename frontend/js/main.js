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

// ==================== QUẢN LÝ CHÍNH SÁCH GIÁ ====================

// Dữ liệu chính sách giá
let pricingPolicy = {
    defaultModel: 'per-download',
    defaultPrice: 500000,
    defaultCurrency: 'VND',
    defaultUsageRights: 'commercial',
    defaultLicense: 'Dữ liệu được cung cấp bởi EV Data Corp...',
    discountEnabled: false,
    bulkDiscount: 0,
    subscriptionDiscount: 0
};

// Cập nhật hiển thị chính sách hiện tại
function updatePolicyDisplay() {
    console.log('🔄 Đang cập nhật hiển thị chính sách...', pricingPolicy);
    
    // Cập nhật mô hình định giá
    const modelElement = document.getElementById('current-pricing-model');
    if (modelElement) {
        modelElement.textContent = getPriceModelText(pricingPolicy.defaultModel);
        console.log('✅ Đã cập nhật mô hình:', pricingPolicy.defaultModel);
    }
    
    // Cập nhật giá mặc định
    const priceElement = document.getElementById('current-default-price');
    if (priceElement) {
        priceElement.textContent = formatPrice(pricingPolicy.defaultPrice) + ' ' + pricingPolicy.defaultCurrency;
        console.log('✅ Đã cập nhật giá:', pricingPolicy.defaultPrice);
    }
    
    // Cập nhật quyền sử dụng
    const usageElement = document.getElementById('current-usage-rights');
    if (usageElement) {
        usageElement.textContent = getUsageRightsText(pricingPolicy.defaultUsageRights);
        console.log('✅ Đã cập nhật quyền sử dụng:', pricingPolicy.defaultUsageRights);
    }
    
    // Cập nhật form trên trang pricing
    const modelSelect = document.getElementById('pricing-model');
    const priceInput = document.getElementById('price-value');
    const usageSelect = document.querySelector('#terms-form select');
    const licenseTextarea = document.querySelector('#terms-form textarea');
    
    if (modelSelect) {
        modelSelect.value = pricingPolicy.defaultModel;
        console.log('✅ Đã cập nhật select model:', pricingPolicy.defaultModel);
    }
    if (priceInput) {
        priceInput.value = pricingPolicy.defaultPrice;
        console.log('✅ Đã cập nhật input price:', pricingPolicy.defaultPrice);
    }
    if (usageSelect) {
        usageSelect.value = pricingPolicy.defaultUsageRights;
        console.log('✅ Đã cập nhật select usage:', pricingPolicy.defaultUsageRights);
    }
    if (licenseTextarea && pricingPolicy.defaultLicense) {
        licenseTextarea.value = pricingPolicy.defaultLicense;
    }
    
    console.log('✅ Hoàn thành cập nhật hiển thị chính sách');
}

// Khởi tạo trang pricing
function initPricingPage() {
    console.log('🚀 Khởi tạo trang pricing...');
    
    // Load pricing policy từ localStorage nếu có
    const savedPricingPolicy = localStorage.getItem('pricingPolicy');
    if (savedPricingPolicy) {
        try {
            pricingPolicy = JSON.parse(savedPricingPolicy);
            console.log('📂 Đã load chính sách từ localStorage:', pricingPolicy);
        } catch (error) {
            console.error('❌ Lỗi khi parse pricingPolicy:', error);
        }
    } else {
        console.log('📂 Không có chính sách trong localStorage, sử dụng mặc định');
    }
    
    // Cập nhật hiển thị chính sách hiện tại
    updatePolicyDisplay();
    
    const form = document.getElementById('pricing-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            savePricingPolicy();
        };
    }
    
    // Xử lý form terms
    const termsForm = document.getElementById('terms-form');
    if (termsForm) {
        termsForm.onsubmit = function(e) {
            e.preventDefault();
            saveTermsPolicy();
        };
    }
    
    initUserDropdown();
    console.log('✅ Hoàn thành khởi tạo trang pricing');
}

// Lưu chính sách giá
function savePricingPolicy() {
    const model = document.getElementById('pricing-model').value;
    const price = parseInt(document.getElementById('price-value').value);
    const currency = document.querySelector('#pricing-form select').value;
    
    if (!model || !price) {
        alert('Vui lòng điền đầy đủ thông tin');
        return;
    }
    
    console.log('💾 Đang lưu chính sách:', { model, price, currency });
    
    // Cập nhật chính sách
    pricingPolicy.defaultModel = model;
    pricingPolicy.defaultPrice = price;
    pricingPolicy.defaultCurrency = currency;
    
    // Lưu vào localStorage
    localStorage.setItem('pricingPolicy', JSON.stringify(pricingPolicy));
    console.log('💾 Đã lưu vào localStorage:', pricingPolicy);
    
    // CẬP NHẬT HIỂN THỊ NGAY LẬP TỨC
    updatePolicyDisplay();
    
    alert(`✅ Đã lưu chính sách giá!\n\nTừ giờ khi thêm dữ liệu mới:\n• Giá sẽ tự động điền: ${formatPrice(price)} ${currency}\n• Mô hình: ${getPriceModelText(model)}`);
}

// Lưu điều khoản sử dụng
function saveTermsPolicy() {
    const usageRights = document.querySelector('#terms-form select').value;
    const licenseText = document.querySelector('#terms-form textarea').value;
    
    if (!usageRights) {
        alert('Vui lòng chọn quyền sử dụng');
        return;
    }
    
    console.log('💾 Đang lưu điều khoản:', { usageRights, licenseText });
    
    // Cập nhật chính sách
    pricingPolicy.defaultUsageRights = usageRights;
    pricingPolicy.defaultLicense = licenseText;
    
    // Lưu vào localStorage
    localStorage.setItem('pricingPolicy', JSON.stringify(pricingPolicy));
    console.log('💾 Đã lưu vào localStorage:', pricingPolicy);
    
    // CẬP NHẬT HIỂN THỊ NGAY LẬP TỨC
    updatePolicyDisplay();
    
    alert('✅ Đã lưu điều khoản sử dụng!');
}

// Thêm hàm hỗ trợ mới
function getUsageRightsText(usage) {
    const rights = {
        'research': 'Chỉ nghiên cứu',
        'commercial': 'Thương mại',
        'internal': 'Nội bộ',
        'extended': 'Mở rộng'
    };
    return rights[usage] || usage;
}

// Áp dụng chính sách giá khi thêm dữ liệu mới
function applyPricingPolicy() {
    const priceInput = document.getElementById('data-price');
    const priceUnitSelect = document.getElementById('price-unit');
    const defaultPriceDisplay = document.getElementById('default-price-display');
    const defaultUnitDisplay = document.getElementById('default-unit-display');
    
    if (priceInput && priceUnitSelect) {
        // Tự động điền giá mặc định
        priceInput.value = pricingPolicy.defaultPrice;
        priceUnitSelect.value = pricingPolicy.defaultModel;
        
        // Hiển thị giá mặc định
        if (defaultPriceDisplay) {
            defaultPriceDisplay.textContent = formatPrice(pricingPolicy.defaultPrice) + ' VND';
        }
        
        // Hiển thị đơn vị giá mặc định
        if (defaultUnitDisplay) {
            defaultUnitDisplay.textContent = getPriceModelText(pricingPolicy.defaultModel);
        }
    }
}

// Dữ liệu data sources với trạng thái admin
const sampleData = {
    dataSources: [
        { 
            id: 1, 
            name: 'Dữ liệu pin EV Model X', 
            type: 'battery', 
            format: 'raw', 
            price: 500000,
            priceUnit: 'per-download',
            status: 'active', 
            downloads: 45,
            description: 'Dữ liệu hiệu suất pin từ 1000+ xe EV Model X',
            tags: 'pin, hiệu suất, ev',
            adminStatus: 'approved',
            adminNote: 'Dữ liệu chất lượng tốt, đã được duyệt'
        },
        { 
            id: 2, 
            name: 'Hành vi lái xe Hà Nội', 
            type: 'driving', 
            format: 'analyzed', 
            price: 1200000,
            priceUnit: 'subscription',
            status: 'active', 
            downloads: 78,
            description: 'Phân tích hành vi lái xe trong khu vực Hà Nội',
            tags: 'hành vi, lái xe, hà nội',
            adminStatus: 'approved',
            adminNote: 'Phân tích chi tiết, hữu ích'
        },
        { 
            id: 3, 
            name: 'Sử dụng trạm sạc TP.HCM', 
            type: 'charging', 
            format: 'raw', 
            price: 750000,
            priceUnit: 'per-download',
            status: 'pending', 
            downloads: 0,
            description: 'Dữ liệu sử dụng trạm sạc tại TP.HCM Q3/2024',
            tags: 'trạm sạc, tp.hcm',
            adminStatus: 'pending',
            adminNote: ''
        },
        { 
            id: 4, 
            name: 'Dữ liệu V2G 2024', 
            type: 'v2g', 
            format: 'raw', 
            price: 900000,
            priceUnit: 'per-download',
            status: 'rejected', 
            downloads: 0,
            description: 'Dữ liệu giao dịch V2G năm 2024',
            tags: 'v2g, giao dịch',
            adminStatus: 'rejected',
            adminNote: 'Dữ liệu chưa đủ chất lượng, cần bổ sung thêm thông tin'
        }
    ],
    stats: {
        downloads: 1247,
        revenue: 245,
        datasets: 48,
        users: 156
    }
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
            console.log('🔄 Đã load pricing policy:', pricingPolicy);
        } catch (error) {
            console.error('❌ Lỗi load pricing policy:', error);
        }
    }
    
    // Cập nhật hiển thị user trên tất cả các trang
    updateUserDisplay();
    
    const path = window.location.pathname;
    const page = path.split('/').pop().replace('.html', '') || 'index';
    
    // Cập nhật menu active
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === page + '.html') {
            link.classList.add('active');
        }
    });
    
    // Khởi tạo trang cụ thể
    // Xem tên page từ đường dẫn; trang provider.html dùng chung dashboard logic
    if ( page === 'dashboard') {
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

// ==================== QUẢN LÝ MODAL ====================

// Đóng modal chỉnh sửa
function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
    currentEditId = null;
}

// Đóng modal khi click outside
function initModalClose() {
    const modal = document.getElementById('edit-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeEditModal();
            }
        });
    }
}

// Đóng modal bằng phím ESC
function initEscapeClose() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });
}

// ==================== QUẢN LÝ CHÍNH SÁCH GIÁ ====================

// Áp dụng chính sách giá khi thêm dữ liệu mới
function applyPricingPolicy() {
    const priceInput = document.getElementById('data-price');
    const priceUnitSelect = document.getElementById('price-unit');
    
    if (priceInput && priceUnitSelect) {
        // Tự động điền giá mặc định
        priceInput.value = pricingPolicy.defaultPrice;
        priceUnitSelect.value = pricingPolicy.defaultModel;
        
        // Hiển thị giá mặc định
        const defaultPriceDisplay = document.getElementById('default-price-display');
        if (defaultPriceDisplay) {
            defaultPriceDisplay.textContent = formatPrice(pricingPolicy.defaultPrice) + ' VND';
        }
    }
}

// Khởi tạo trang pricing
function initPricingPage() {
    // Điền dữ liệu hiện tại vào form
    document.getElementById('pricing-model').value = pricingPolicy.defaultModel;
    document.getElementById('price-value').value = pricingPolicy.defaultPrice;
    
    const form = document.getElementById('pricing-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            savePricingPolicy();
        };
    }
    
    // Xử lý form terms
    const termsForm = document.getElementById('terms-form');
    if (termsForm) {
        termsForm.onsubmit = function(e) {
            e.preventDefault();
            alert('✅ Đã lưu điều khoản sử dụng');
        };
    }
    
    initUserDropdown();
}

// Lưu chính sách giá
function savePricingPolicy() {
    const model = document.getElementById('pricing-model').value;
    const price = parseInt(document.getElementById('price-value').value);
    
    if (!model || !price) {
        alert('Vui lòng điền đầy đủ thông tin');
        return;
    }
    
    // Cập nhật chính sách
    pricingPolicy.defaultModel = model;
    pricingPolicy.defaultPrice = price;
    
    // Lưu vào localStorage
    localStorage.setItem('pricingPolicy', JSON.stringify(pricingPolicy));
    
    alert(`✅ Đã lưu chính sách giá!\n\nTừ giờ khi thêm dữ liệu mới:\n• Giá sẽ tự động điền: ${formatPrice(price)} VND\n• Mô hình: ${getPriceModelText(model)}`);
}

// ==================== DASHBOARD ====================

function initDashboard() {
    // Cập nhật stats
    document.getElementById('stat-downloads').textContent = sampleData.stats.downloads.toLocaleString();
    document.getElementById('stat-revenue').textContent = sampleData.stats.revenue + 'M VND';
    document.getElementById('stat-datasets').textContent = sampleData.stats.datasets;
    document.getElementById('stat-users').textContent = sampleData.stats.users;
    initUserDropdown();
}

// ==================== TRANG QUẢN LÝ DỮ LIỆU ====================

function initDataPage() {
    loadDataTable();
    initFileUpload();
    initSearch();
    initUserDropdown();
    initModalClose();
    initEscapeClose();
    
    // ÁP DỤNG CHÍNH SÁCH GIÁ - TỰ ĐỘNG ĐIỀN GIÁ MẶC ĐỊNH!
    applyPricingPolicy();
    
    // Xử lý form thêm dữ liệu
    const form = document.getElementById('add-data-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            addDataSource();
        };
    }

    // Xử lý form chỉnh sửa
    const editForm = document.getElementById('edit-data-form');
    if (editForm) {
        editForm.onsubmit = function(e) {
            e.preventDefault();
            saveEditedData();
        };
    }
}

// Tải danh sách dữ liệu vào table
function loadDataTable() {
    const tableBody = document.getElementById('data-sources-body');
    if (tableBody) {
        tableBody.innerHTML = sampleData.dataSources.map(data => `
            <tr>
                <td>
                    <div style="font-weight: 600;">${data.name}</div>
                    <div style="color: var(--gray-light); font-size: 0.8rem;">${data.description.substring(0, 50)}...</div>
                </td>
                <td>${getDataTypeText(data.type)}</td>
                <td>${getDataFormatText(data.format)}</td>
                <td>
                    <div class="price-badge">${formatPrice(data.price)} VND</div>
                    <div style="color: var(--gray-light); font-size: 0.8rem; margin-top: 0.25rem;">
                        ${getPriceUnitText(data.priceUnit)}
                    </div>
                </td>
                <td>
                    <span class="${getStatusClass(data.status, data.adminStatus)}">
                        ${getStatusText(data.status, data.adminStatus)}
                    </span>
                    ${data.adminStatus === 'rejected' ? `
                        <div style="margin-top: 0.25rem;">
                            <button class="btn-resubmit" onclick="resubmitData(${data.id})" style="background: none; border: none; color: var(--accent); font-size: 0.8rem; cursor: pointer;">
                                <i class="fas fa-redo"></i> Gửi lại
                            </button>
                        </div>
                    ` : ''}
                </td>
                <td>${data.downloads}</td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary" onclick="openEditModal(${data.id})">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn btn-outline" onclick="deleteDataSource(${data.id})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
}

// Khởi tạo upload file
function initFileUpload() {
    const uploadArea = document.getElementById('file-upload-area');
    const fileInput = document.getElementById('data-file');
    const fileInfo = document.getElementById('file-info');
    
    if (uploadArea && fileInput) {
        // Click để chọn file
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }
}

// Xử lý file được chọn
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

// Xóa file đã chọn
function removeFile() {
    const fileInput = document.getElementById('data-file');
    const fileInfo = document.getElementById('file-info');
    
    fileInput.value = '';
    fileInfo.style.display = 'none';
}

// Thêm dữ liệu mới
function addDataSource() {
    const name = document.getElementById('data-name').value;
    const type = document.getElementById('data-type').value;
    const format = document.getElementById('data-format').value;
    const price = parseInt(document.getElementById('data-price').value);
    const priceUnit = document.getElementById('price-unit').value;
    const description = document.getElementById('data-description').value;
    const tags = document.getElementById('data-tags').value;
    const fileInput = document.getElementById('data-file');
    
    if (!name || !type || !format || !price || !fileInput.files[0]) {
        alert('Vui lòng điền đầy đủ thông tin và chọn file dữ liệu');
        return;
    }

    // Thêm vào data mẫu
    const newData = {
        id: sampleData.dataSources.length + 1,
        name: name,
        type: type,
        format: format,
        price: price,
        priceUnit: priceUnit,
        status: 'pending',
        downloads: 0,
        description: description,
        tags: tags,
        fileName: fileInput.files[0].name,
        fileSize: (fileInput.files[0].size / (1024 * 1024)).toFixed(2),
        adminStatus: 'pending',
        adminNote: ''
    };
    
    sampleData.dataSources.push(newData);
    sampleData.stats.datasets += 1;

    alert(`✅ Đã thêm bộ dữ liệu: ${name}\n💰 Giá: ${formatPrice(price)} VND\n⏳ Đang chờ admin duyệt...`);
    
    // Reset form
    resetForm();
    
    // Reload data table
    loadDataTable();
}

// Reset form
function resetForm() {
    document.getElementById('add-data-form').reset();
    removeFile();
    // Sau khi reset, vẫn áp dụng giá mặc định
    applyPricingPolicy();
}

// Mở modal chỉnh sửa
function openEditModal(id) {
    const data = sampleData.dataSources.find(d => d.id === id);
    if (data) {
        currentEditId = id;
        
        // Điền dữ liệu cơ bản - Provider có thể sửa
        document.getElementById('edit-data-id').value = data.id;
        document.getElementById('edit-data-name').value = data.name;
        document.getElementById('edit-data-type').value = data.type;
        document.getElementById('edit-data-format').value = data.format;
        document.getElementById('edit-data-price').value = data.price;
        document.getElementById('edit-price-unit').value = data.priceUnit;
        document.getElementById('edit-data-description').value = data.description;
        
        // Quản lý trạng thái - Provider chỉ xem, không sửa được
        const statusInfo = document.getElementById('status-info');
        const pauseControl = document.getElementById('pause-control');
        const pauseBtn = document.getElementById('pause-btn');
        const resumeBtn = document.getElementById('resume-btn');
        
        if (data.adminStatus === 'approved') {
            // Đã được duyệt - Provider có thể tạm ngừng/tiếp tục
            if (data.status === 'active') {
                statusInfo.className = 'status-info active';
                statusInfo.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-check-circle"></i>
                        <strong>Đang hoạt động</strong>
                    </div>
                    <div style="font-size: 0.9rem;">
                        <strong>Ghi chú từ Admin:</strong> ${data.adminNote}
                    </div>
                `;
                pauseControl.style.display = 'block';
                pauseBtn.style.display = 'block';
                resumeBtn.style.display = 'none';
            } else {
                statusInfo.className = 'status-info inactive';
                statusInfo.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-pause-circle"></i>
                        <strong>Đã tạm ngừng</strong>
                    </div>
                    <div style="font-size: 0.9rem;">
                        <strong>Ghi chú từ Admin:</strong> ${data.adminNote}
                    </div>
                    <div style="font-size: 0.9rem; margin-top: 0.5rem;">
                        <em>Bạn đã tạm ngừng bán dữ liệu này</em>
                    </div>
                `;
                pauseControl.style.display = 'block';
                pauseBtn.style.display = 'none';
                resumeBtn.style.display = 'block';
            }
        } else if (data.adminStatus === 'rejected') {
            // Bị từ chối - Provider phải sửa và gửi lại
            statusInfo.className = 'status-info rejected';
            statusInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-times-circle"></i>
                    <strong>Đã bị từ chối</strong>
                </div>
                <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                    <strong>Lý do từ chối:</strong> ${data.adminNote}
                </div>
                <div style="font-size: 0.9rem; color: var(--accent);">
                    <i class="fas fa-info-circle"></i> Vui lòng chỉnh sửa thông tin và lưu để gửi lại admin duyệt
                </div>
            `;
            pauseControl.style.display = 'none';
        } else {
            // Đang chờ duyệt
            statusInfo.className = 'status-info pending';
            statusInfo.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-clock"></i>
                    <strong>Đang chờ duyệt</strong>
                </div>
                <div style="font-size: 0.9rem;">
                    Dữ liệu của bạn đang được admin xem xét và phê duyệt.
                </div>
                <div style="font-size: 0.9rem; margin-top: 0.5rem; color: var(--accent);">
                    <i class="fas fa-info-circle"></i> Bạn có thể chỉnh sửa thông tin trong khi chờ duyệt
                </div>
            `;
            pauseControl.style.display = 'none';
        }
        
        // Hiển thị modal
        document.getElementById('edit-modal').style.display = 'flex';
    }
}

// Lưu dữ liệu đã chỉnh sửa - Chỉ lưu thông tin cơ bản
function saveEditedData() {
    if (!currentEditId) return;
    
    const data = sampleData.dataSources.find(d => d.id === currentEditId);
    if (data) {
        // Cập nhật dữ liệu cơ bản - Provider có thể sửa
        data.name = document.getElementById('edit-data-name').value;
        data.type = document.getElementById('edit-data-type').value;
        data.format = document.getElementById('edit-data-format').value;
        data.price = parseInt(document.getElementById('edit-data-price').value);
        data.priceUnit = document.getElementById('edit-price-unit').value;
        data.description = document.getElementById('edit-data-description').value;
        
        // Nếu bị từ chối, khi sửa sẽ chuyển về pending để admin duyệt lại
        if (data.adminStatus === 'rejected') {
            data.status = 'pending';
            data.adminStatus = 'pending';
            data.adminNote = '';
        }
        
        alert('✅ Đã cập nhật thông tin dữ liệu thành công');
        closeEditModal();
        loadDataTable();
    }
}

// Tạm ngừng dữ liệu (chỉ khi đã được duyệt)
function togglePauseData() {
    if (!currentEditId) return;
    
    const data = sampleData.dataSources.find(d => d.id === currentEditId);
    if (data && data.adminStatus === 'approved') {
        data.status = 'inactive';
        alert('⏸️ Đã tạm ngừng bán dữ liệu');
        closeEditModal();
        loadDataTable();
    }
}

// Tiếp tục dữ liệu (chỉ khi đã được duyệt)
function toggleResumeData() {
    if (!currentEditId) return;
    
    const data = sampleData.dataSources.find(d => d.id === currentEditId);
    if (data && data.adminStatus === 'approved') {
        data.status = 'active';
        alert('▶️ Đã tiếp tục bán dữ liệu');
        closeEditModal();
        loadDataTable();
    }
}

// Xóa dữ liệu
function deleteDataSource(id) {
    if (confirm('Bạn có chắc muốn xóa bộ dữ liệu này?')) {
        sampleData.dataSources = sampleData.dataSources.filter(d => d.id !== id);
        sampleData.stats.datasets -= 1;
        alert('✅ Đã xóa dữ liệu');
        loadDataTable();
    }
}

// Tìm kiếm dữ liệu
function initSearch() {
    const searchInput = document.getElementById('search-data');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredData = sampleData.dataSources.filter(data => 
                data.name.toLowerCase().includes(searchTerm) ||
                data.description.toLowerCase().includes(searchTerm) ||
                data.tags.toLowerCase().includes(searchTerm)
            );
            
            const tableBody = document.getElementById('data-sources-body');
            if (tableBody) {
                tableBody.innerHTML = filteredData.map(data => `
                    <tr>
                        <td>
                            <div style="font-weight: 600;">${data.name}</div>
                            <div style="color: var(--gray-light); font-size: 0.8rem;">${data.description.substring(0, 50)}...</div>
                        </td>
                        <td>${getDataTypeText(data.type)}</td>
                        <td>${getDataFormatText(data.format)}</td>
                        <td>
                            <div class="price-badge">${formatPrice(data.price)} VND</div>
                            <div style="color: var(--gray-light); font-size: 0.8rem; margin-top: 0.25rem;">
                                ${getPriceUnitText(data.priceUnit)}
                            </div>
                        </td>
                        <td>
                            <span class="${getStatusClass(data.status, data.adminStatus)}">
                                ${getStatusText(data.status, data.adminStatus)}
                            </span>
                            ${data.adminStatus === 'rejected' ? `
                                <div style="margin-top: 0.25rem;">
                                    <button class="btn-resubmit" onclick="resubmitData(${data.id})" style="background: none; border: none; color: var(--accent); font-size: 0.8rem; cursor: pointer;">
                                        <i class="fas fa-redo"></i> Gửi lại
                                    </button>
                                </div>
                            ` : ''}
                        </td>
                        <td>${data.downloads}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-primary" onclick="openEditModal(${data.id})">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button class="btn btn-outline" onclick="deleteDataSource(${data.id})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        });
    }
}

// Làm mới dữ liệu
function refreshData() {
    loadDataTable();
    alert('🔄 Đã làm mới dữ liệu');
}

// Gửi lại dữ liệu bị từ chối
function resubmitData(id) {
    const data = sampleData.dataSources.find(d => d.id === id);
    if (data && data.adminStatus === 'rejected') {
        data.status = 'pending';
        data.adminStatus = 'pending';
        data.adminNote = '';
        alert('✅ Đã gửi lại dữ liệu để admin duyệt');
        loadDataTable();
    }
}

// ==================== TRANG DOANH THU ====================

function initRevenuePage() {
    initCharts();
    initUserDropdown();
}

// ==================== TRANG BẢO MẬT ====================

function initPrivacyPage() {
    const form = document.getElementById('privacy-form');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            savePrivacySettings();
        };
    }
    initUserDropdown();
}

function savePrivacySettings() {
    const anonymize = document.getElementById('anonymize-data').checked;
    const standard = document.getElementById('privacy-standard').value;
    
    alert(`✅ Đã lưu cài đặt bảo mật:\nẨn danh hóa: ${anonymize ? 'Bật' : 'Tắt'}\nTiêu chuẩn: ${standard}`);
}

// ==================== TRANG CÀI ĐẶT TÀI KHOẢN ====================

function initSettingsPage() {
    loadUserData();
    initUserDropdown();
    
    const forms = ['company-form', 'login-form', 'system-settings-form', 'notification-form'];
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                handleFormSubmit(formId);
            };
        }
    });
}

// Tải dữ liệu user
function loadUserData() {
    // Điền dữ liệu vào form
    document.getElementById('company-name').value = userData.companyName;
    document.getElementById('company-email').value = userData.email;
    document.getElementById('company-phone').value = userData.phone;
    document.getElementById('contact-person').value = userData.contactPerson;
    document.getElementById('company-address').value = userData.address;
    document.getElementById('company-description').value = userData.description;
    
    // Cập nhật tên hiển thị
    updateUserDisplay();
}

// Xử lý submit form
function handleFormSubmit(formId) {
    switch(formId) {
        case 'company-form':
            updateCompanyInfo();
            break;
        case 'login-form':
            changePassword();
            break;
        case 'system-settings-form':
            saveSystemSettings();
            break;
        case 'notification-form':
            saveNotificationSettings();
            break;
    }
}

// Cập nhật thông tin công ty
function updateCompanyInfo() {
    const newCompanyName = document.getElementById('company-name').value;
    const newEmail = document.getElementById('company-email').value;
    const newPhone = document.getElementById('company-phone').value;
    const newContactPerson = document.getElementById('contact-person').value;
    const newAddress = document.getElementById('company-address').value;
    const newDescription = document.getElementById('company-description').value;
    
    if (!newCompanyName || !newEmail || !newContactPerson) {
        alert('Vui lòng điền đầy đủ thông tin bắt buộc');
        return;
    }
    
    // Cập nhật user data
    userData.companyName = newCompanyName;
    userData.email = newEmail;
    userData.phone = newPhone;
    userData.contactPerson = newContactPerson;
    userData.address = newAddress;
    userData.description = newDescription;
    
    // Cập nhật hiển thị
    updateUserDisplay();
    
    alert('✅ Đã cập nhật thông tin công ty thành công');
}

// Cập nhật hiển thị user
function updateUserDisplay() {
    // Cập nhật tên công ty ở header
    const companyNameElements = document.querySelectorAll('#user-company-name');
    companyNameElements.forEach(element => {
        element.textContent = userData.companyName;
    });
    
    // Cập nhật avatar (lấy chữ cái đầu)
    const avatarElements = document.querySelectorAll('#user-avatar');
    avatarElements.forEach(element => {
        element.textContent = userData.companyName.charAt(0);
    });
    
    // Lưu vào localStorage để các trang khác có thể sử dụng
    localStorage.setItem('userData', JSON.stringify(userData));
}

// Đổi mật khẩu
function changePassword() {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (!newPassword && !confirmPassword) {
        alert('Vui lòng nhập mật khẩu mới');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('Mật khẩu xác nhận không khớp');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Mật khẩu phải có ít nhất 6 ký tự');
        return;
    }
    
    alert('✅ Đã đổi mật khẩu thành công');
    document.getElementById('login-form').reset();
}

// Lưu cài đặt hệ thống
function saveSystemSettings() {
    const language = document.getElementById('language').value;
    const timezone = document.getElementById('timezone').value;
    const dateFormat = document.getElementById('date-format').value;
    const currency = document.getElementById('currency').value;
    
    // Lưu vào localStorage
    const systemSettings = {
        language: language,
        timezone: timezone,
        dateFormat: dateFormat,
        currency: currency
    };
    localStorage.setItem('systemSettings', JSON.stringify(systemSettings));
    
    alert('✅ Đã lưu cài đặt hệ thống');
}

// Lưu cài đặt thông báo
function saveNotificationSettings() {
    const emailNotifications = document.getElementById('email-notifications').checked;
    const securityNotifications = document.getElementById('security-notifications').checked;
    const weeklyReports = document.getElementById('weekly-reports').checked;
    
    const notificationSettings = {
        email: emailNotifications,
        security: securityNotifications,
        weeklyReports: weeklyReports
    };
    localStorage.setItem('notificationSettings', JSON.stringify(notificationSettings));
    
    alert('✅ Đã lưu cài đặt thông báo');
}

// ==================== QUẢN LÝ USER DROPDOWN ====================

// Khởi tạo dropdown user
function initUserDropdown() {
    const userInfo = document.getElementById('user-info-dropdown');
    const dropdown = document.getElementById('user-dropdown');
    
    if (userInfo && dropdown) {
        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        
        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function() {
            dropdown.classList.remove('show');
        });
    }
}

// Hiển thị profile
function showProfile() {
    alert(`👤 Thông tin công ty:\n\n🏢 Tên: ${userData.companyName}\n📧 Email: ${userData.email}\n📞 Điện thoại: ${userData.phone}\n👤 Người liên hệ: ${userData.contactPerson}\n📍 Địa chỉ: ${userData.address}`);
}

// Hiển thị settings
function showSettings() {
    window.location.href = 'settings.html';
}

// Đăng xuất
function logout() {
    if (confirm('Bạn có chắc muốn đăng xuất?')) {
        // Xóa dữ liệu đăng nhập
        localStorage.removeItem('userToken');
        alert('👋 Đã đăng xuất thành công!');
        
        // Chuyển về trang đăng nhập (trong thực tế)
        // window.location.href = 'login.html';
        
        // Trong demo, reload trang
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Đăng xuất tất cả thiết bị
function logoutAllDevices() {
    if (confirm('Bạn có chắc muốn đăng xuất khỏi tất cả thiết bị?')) {
        // Xóa tất cả session
        localStorage.clear();
        alert('🔐 Đã đăng xuất khỏi tất cả thiết bị');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Xóa tài khoản
function deleteAccount() {
    const confirmText = prompt('⚠️ CẢNH BÁO NGUY HIỂM!\n\nViết "DELETE" để xác nhận xóa tài khoản vĩnh viễn:');
    
    if (confirmText === 'DELETE') {
        alert('🗑️ Tài khoản sẽ bị xóa vĩnh viễn...');
        // Trong thực tế, gọi API xóa tài khoản
        setTimeout(() => {
            localStorage.clear();
            alert('✅ Tài khoản đã bị xóa');
            window.location.href = 'index.html';
        }, 2000);
    } else {
        alert('❌ Hủy xóa tài khoản');
    }
}

// ==================== HÀM HỖ TRỢ ====================

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

function getPriceModelText(model) {
    const models = {
        'per-download': 'Theo lượt tải',
        'subscription': 'Theo gói thuê bao',
        'capacity': 'Theo dung lượng dữ liệu',
        'api': 'Truy cập API',
        'one-time': 'Một lần'
    };
    return models[model] || model;
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

function getStatusText(status, adminStatus) {
    if (adminStatus === 'rejected') {
        return 'Bị từ chối';
    } else if (adminStatus === 'pending') {
        return 'Chờ duyệt';
    } else if (status === 'inactive') {
        return 'Tạm ngừng';
    } else {
        return 'Đang hoạt động';
    }
}

function getStatusClass(status, adminStatus) {
    if (adminStatus === 'rejected') {
        return 'status-rejected';
    } else if (adminStatus === 'pending') {
        return 'status-pending';
    } else if (status === 'inactive') {
        return 'status-inactive';
    } else {
        return 'status-active';
    }
}

// ==================== BIỂU ĐỒ DOANH THU ====================

// Biến toàn cục cho biểu đồ
let revenueChart, topProductsChart;

// Dữ liệu mẫu cho biểu đồ
const revenueData = {
    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
    datasets: [{
        label: 'Doanh thu (triệu VND)',
        data: [45, 52, 38, 65, 72, 68, 80, 95, 88, 105, 98, 120],
        borderColor: '#00d4ff',
        backgroundColor: 'rgba(0, 212, 255, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4
    }]
};

const topProductsData = {
    labels: ['Dữ liệu pin', 'Hành vi lái xe', 'Trạm sạc', 'V2G', 'Khác'],
    datasets: [{
        label: 'Doanh thu (triệu VND)',
        data: [85, 62, 45, 28, 15],
        backgroundColor: [
            '#2563eb',
            '#06b6d4',
            '#00d4ff',
            '#3b82f6',
            '#64748b'
        ],
        borderColor: '#0f172a',
        borderWidth: 2
    }]
};

// Khởi tạo biểu đồ
function initCharts() {
    // Biểu đồ doanh thu
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#94a3b8',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#00d4ff',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(100, 116, 139, 0.2)'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(100, 116, 139, 0.2)'
                    },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value) {
                            return value + 'M';
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ top sản phẩm
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    topProductsChart = new Chart(topProductsCtx, {
        type: 'bar',
        data: topProductsData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff'
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(100, 116, 139, 0.2)'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(100, 116, 139, 0.2)'
                    },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value) {
                            return value + 'M';
                        }
                    }
                }
            }
        }
    });
}

// Cập nhật loại biểu đồ
function updateChartType() {
    const type = document.getElementById('chart-type').value;
    revenueChart.config.type = type;
    revenueChart.update();
}

// Export biểu đồ
function exportChart() {
    const link = document.createElement('a');
    link.download = 'doanh-thu-ev-data.png';
    link.href = revenueChart.toBase64Image();
    link.click();
}

const API_BASE = "http://localhost:3000/api";

fetch(`${API_BASE}/data`)
  .then(res => res.json())
  .then(data => {
    console.log("Dữ liệu từ backend:", data);
    // xử lý hiển thị data ở đây
  })
  .catch(err => console.error("Lỗi khi gọi API:", err));

