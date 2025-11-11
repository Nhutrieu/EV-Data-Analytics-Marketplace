function toggleMenu(id) {
    document.querySelectorAll('.submenu').forEach(el => el.style.display='none');
    const sub = document.getElementById('submenu-'+id);
    if (sub) sub.style.display='block';
}

// Modal
function openModal(type, user=null) {
    const m = document.getElementById('modal');
    m.style.display = 'block';
    m.dataset.type = type;
    document.getElementById('modalTitle').innerText = type==='add' ? 'Thêm User' : 'Chỉnh sửa User';
    if(user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('userName').value = user.name;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userRole').value = user.role;
    } else {
        document.getElementById('userId').value='';
        document.getElementById('userName').value='';
        document.getElementById('userEmail').value='';
        document.getElementById('userRole').value='provider';
    }
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

// AJAX CRUD
async function saveUser() {
    const id = document.getElementById('userId').value;
    const name = document.getElementById('userName').value;
    const email = document.getElementById('userEmail').value;
    const role = document.getElementById('userRole').value;

    const data = new FormData();
    data.append('action', id ? 'edit' : 'add');
    if(id) data.append('id', id);
    data.append('name', name);
    data.append('email', email);
    data.append('role', role);

    const res = await fetch('ajax_user.php', { method:'POST', body:data });
    const json = await res.json();
    if(json.ok) {
        alert('Cập nhật thành công!');
        location.reload();
    } else {
        alert('Lỗi: ' + json.error);
    }
}

async function deleteUser(id) {
    if(!confirm('Bạn có chắc muốn xóa user này?')) return;
    const data = new FormData();
    data.append('action','delete');
    data.append('id',id);
    const res = await fetch('ajax_user.php',{method:'POST', body:data});
    const json = await res.json();
    if(json.ok) location.reload();
    else alert('Lỗi: ' + json.error);
}

async function toggleApiKey(id) {
    const data = new FormData();
    data.append('action','toggle_apikey');
    data.append('id',id);
    const res = await fetch('ajax_user.php',{method:'POST',body:data});
    const json = await res.json();
    if(json.api_key) alert('API Key: ' + json.api_key);
    else alert('Đã thu hồi API Key');
}
