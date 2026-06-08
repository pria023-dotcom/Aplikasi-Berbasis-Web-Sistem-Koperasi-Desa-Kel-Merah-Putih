/* assets/js/main.js */

// Global state variables populated from page
let membersData = [];

document.addEventListener('DOMContentLoaded', () => {
    initClock();
    initSidebar();
    initRouting();
    initFormWatchers();
    
    // Read members list from index page if available
    // We can query them dynamically or parse from DOM table
    extractMembersFromDOM();
});

// ==========================================
// 1. LIVE TIME TRACKER
// ==========================================
function initClock() {
    const clockElement = document.getElementById('live-clock');
    
    function updateClock() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const day = String(now.getDate()).padStart(2, '0');
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        if (clockElement) {
            clockElement.textContent = `${dayName}, ${day} ${monthName} ${year} | ${hours}:${minutes}:${seconds}`;
        }
    }
    
    updateClock();
    setInterval(updateClock, 1000);
}

// ==========================================
// 2. SIDEBAR COLLAPSE / DRAWER
// ==========================================
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    
    // Load preference
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
    }
    
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        });
    }
}

// ==========================================
// 3. SPA ROUTING VIA TABS & HASH
// ==========================================
function initRouting() {
    const navItems = document.querySelectorAll('.nav-item');
    const panels = document.querySelectorAll('.module-panel');
    const title = document.getElementById('current-page-title');
    const subtitle = document.getElementById('current-page-subtitle');
    
    const pageSubtitles = {
        'dashboard': 'Koperasi Desa/Kelurahan Merah Putih - Layanan Terintegrasi & Modern',
        'keanggotaan': 'Manajemen Data & Administrasi Anggota Koperasi',
        'ritel': 'Inventarisasi & Penjualan Unit Usaha Ritel Desa',
        'logistik': 'Sistem Transportasi & Logistik Hasil Tani & Barang Ritel',
        'gudang': 'Pencatatan Stok & Kualitas Hasil Pertanian Desa',
        'permodalan': 'Struktur Ekuitas & Sumber Dana Koperasi',
        'pendanaan_sal': 'Alokasi Dana Likuiditas untuk Kesejahteraan Desa',
        'jaminan_desa': 'Penjaminan Proyek Pembangunan Desa Berbasis Sinergi',
        'simpan_pinjam': 'Layanan Kredit & Simpanan Anggota Koperasi',
        'shu': 'Kalkulasi Otomatis SHU Proporsional Anggota Terdaftar',
        'info_desa': 'Sistem Publikasi Pengumuman & Berita Koperasi Desa/Kelurahan Merah Putih'
    };

    function switchTab(tabId) {
        let found = false;
        panels.forEach(panel => {
            if (panel.id === tabId) {
                panel.classList.add('active');
                found = true;
            } else {
                panel.classList.remove('active');
            }
        });
        
        if (!found) {
            // Default to dashboard
            document.getElementById('dashboard').classList.add('active');
            tabId = 'dashboard';
        }
        
        navItems.forEach(item => {
            if (item.getAttribute('data-tab') === tabId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Update Title & Header Subtitle
        if (title) {
            const tabName = document.querySelector(`.nav-item[data-tab="${tabId}"] span`)?.textContent || 'Dashboard';
            title.textContent = tabName;
            subtitle.textContent = pageSubtitles[tabId] || pageSubtitles['dashboard'];
        }
    }
    
    // Event listener on tabs click
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = item.getAttribute('data-tab');
            window.location.hash = tabId;
            switchTab(tabId);
        });
    });
    
    // Check initial hash
    const initialHash = window.location.hash.substring(1);
    if (initialHash) {
        switchTab(initialHash);
    }
    
    // Watch hash change
    window.addEventListener('hashchange', () => {
        const hash = window.location.hash.substring(1);
        if (hash) switchTab(hash);
    });
}

// ==========================================
// 4. PARSE MEMBERS FOR SELECT BOXES
// ==========================================
function extractMembersFromDOM() {
    // Parse keanggotaan table to extract member IDs and names
    const memberRows = document.querySelectorAll('#keanggotaan tbody tr');
    membersData = [];
    memberRows.forEach(row => {
        // Edit button has member JSON data
        const editBtn = row.querySelector('.bi-edit');
        if (editBtn) {
            // Extracted from inline onclick attribute or search pattern
            const onclickAttr = editBtn.getAttribute('onclick');
            const match = onclickAttr.match(/editMember\((.*?)\)/);
            if (match && match[1]) {
                try {
                    const memberData = JSON.parse(match[1]);
                    membersData.push(memberData);
                } catch(e) {}
            }
        }
    });
}

// ==========================================
// 5. MODAL SYSTEM & CRUD GENERATORS
// ==========================================
const modalOverlay = document.getElementById('crud-modal');
const modalTitle = document.getElementById('modal-title');
const formAction = document.getElementById('form-action');
const formId = document.getElementById('form-id');
const fieldsContainer = document.getElementById('dynamic-fields-container');
const modalForm = document.getElementById('modal-form');

function openModal() {
    if (modalOverlay) modalOverlay.classList.add('active');
}

function closeModal() {
    if (modalOverlay) {
        modalOverlay.classList.remove('active');
        modalForm.reset();
    }
}

// Watchers for form behavior (like toggling loan options)
function initFormWatchers() {
    fieldsContainer.addEventListener('change', (e) => {
        if (e.target.name === 'type' && e.target.closest('#crud-modal')) {
            const loanFields = document.querySelectorAll('.loan-only-field');
            if (e.target.value === 'Pinjaman') {
                loanFields.forEach(f => f.style.display = 'flex');
            } else {
                loanFields.forEach(f => f.style.display = 'none');
            }
        }
    });
}

// Generate appropriate fields based on module type
function openAddModal(module) {
    formId.value = '';
    
    // Map module names to server-side action names
    const actionMapping = {
        'keanggotaan': 'add_member',
        'ritel': 'add_goods',
        'logistik': 'add_logistics',
        'gudang': 'add_warehouse',
        'permodalan': 'add_capital',
        'pendanaan_sal': 'add_sal',
        'jaminan_desa': 'add_guarantee',
        'simpan_pinjam': 'add_simpan_pinjam',
        'info_desa': 'add_info'
    };
    
    formAction.value = actionMapping[module] || `add_${module}`;
    modalTitle.textContent = `Tambah Data ${getModuleLabel(module)}`;
    
    // Generate Fields HTML
    fieldsContainer.innerHTML = getFieldsHTML(module);
    
    // Pre-fill date fields with today
    const dateInputs = fieldsContainer.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => input.value = today);
    
    openModal();
}

function getModuleLabel(module) {
    const labels = {
        'keanggotaan': 'Anggota',
        'ritel': 'Barang Ritel',
        'logistik': 'Logistik Pengiriman',
        'gudang': 'Hasil Pertanian',
        'permodalan': 'Sumber Modal',
        'pendanaan_sal': 'Penyaluran SAL',
        'jaminan_desa': 'Jaminan Proyek',
        'simpan_pinjam': 'Simpan Pinjam',
        'info_desa': 'Informasi Desa'
    };
    return labels[module] || 'Data';
}

function getFieldsHTML(module, data = null) {
    const val = (field) => data ? (data[field] ?? '') : '';
    
    switch(module) {
        case 'keanggotaan':
            return `
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" value="${val('name')}" required>
                </div>
                <div class="form-group">
                    <label>NIK (16 Digit)</label>
                    <input type="text" class="form-control" name="nik" maxlength="16" value="${val('nik')}" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" class="form-control" name="phone" value="${val('phone')}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Bergabung</label>
                    <input type="date" class="form-control" name="join_date" value="${val('join_date')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Alamat Lengkap</label>
                    <textarea class="form-control" name="address" rows="3" required>${val('address')}</textarea>
                </div>
                <div class="form-group">
                    <label>Status Anggota</label>
                    <select class="form-control" name="status" required>
                        <option value="Aktif" ${val('status') === 'Aktif' ? 'selected' : ''}>Aktif</option>
                        <option value="Non-Aktif" ${val('status') === 'Non-Aktif' ? 'selected' : ''}>Non-Aktif</option>
                    </select>
                </div>
            `;
            
        case 'ritel':
            return `
                <div class="form-group">
                    <label>Kode Barang</label>
                    <input type="text" class="form-control" name="code" placeholder="BRG001" value="${val('code')}" required>
                </div>
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" class="form-control" name="name" value="${val('name')}" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select class="form-control" name="category" required>
                        <option value="Sembako" ${val('category') === 'Sembako' ? 'selected' : ''}>Sembako</option>
                        <option value="Pertanian" ${val('category') === 'Pertanian' ? 'selected' : ''}>Pertanian</option>
                        <option value="Peternakan" ${val('category') === 'Peternakan' ? 'selected' : ''}>Peternakan</option>
                        <option value="Lainnya" ${val('category') === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stok Awal</label>
                    <input type="number" class="form-control" name="stock" value="${val('stock') || 0}" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli (Rp)</label>
                    <input type="number" class="form-control" name="buy_price" value="${val('buy_price') || 0}" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual (Rp)</label>
                    <input type="number" class="form-control" name="sell_price" value="${val('sell_price') || 0}" required>
                </div>
                <div class="form-group col-full">
                    <label>Nama Pemasok (Supplier)</label>
                    <input type="text" class="form-control" name="supplier" value="${val('supplier')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Cabang Penempatan</label>
                    <select class="form-control" name="branch" required>
                        <option value="Pusat" ${val('branch') === 'Pusat' ? 'selected' : ''}>Toko Pusat</option>
                        <option value="Cabang Dusun I" ${val('branch') === 'Cabang Dusun I' ? 'selected' : ''}>Cabang Dusun I</option>
                        <option value="Cabang Dusun II" ${val('branch') === 'Cabang Dusun II' ? 'selected' : ''}>Cabang Dusun II</option>
                    </select>
                </div>
            `;
            
        case 'logistik':
            return `
                <div class="form-group">
                    <label>Nomor Tracking / Resi (Kosongkan untuk Auto)</label>
                    <input type="text" class="form-control" name="tracking_number" placeholder="TRKxxxxxxxx" value="${val('tracking_number')}">
                </div>
                <div class="form-group">
                    <label>Muatan / Cargo</label>
                    <input type="text" class="form-control" name="cargo" placeholder="Beras, Pupuk dll" value="${val('cargo')}" required>
                </div>
                <div class="form-group">
                    <label>Pengirim</label>
                    <input type="text" class="form-control" name="sender" value="${val('sender')}" required>
                </div>
                <div class="form-group">
                    <label>Penerima</label>
                    <input type="text" class="form-control" name="receiver" value="${val('receiver')}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kirim</label>
                    <input type="date" class="form-control" name="ship_date" value="${val('ship_date')}" required>
                </div>
                <div class="form-group">
                    <label>Status Pengiriman</label>
                    <select class="form-control" name="status" required>
                        <option value="Pending" ${val('status') === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Diproses" ${val('status') === 'Diproses' ? 'selected' : ''}>Diproses</option>
                        <option value="Dalam Perjalanan" ${val('status') === 'Dalam Perjalanan' ? 'selected' : ''}>Dalam Perjalanan</option>
                        <option value="Diterima" ${val('status') === 'Diterima' ? 'selected' : ''}>Diterima</option>
                    </select>
                </div>
            `;
            
        case 'gudang':
            return `
                <div class="form-group">
                    <label>Nama Komoditas Tani</label>
                    <input type="text" class="form-control" name="commodity" placeholder="Padi, Jagung, Kacang" value="${val('commodity')}" required>
                </div>
                <div class="form-group">
                    <label>Kuantitas (Ton / Kg)</label>
                    <input type="number" step="0.01" class="form-control" name="quantity" placeholder="8.5" value="${val('quantity')}" required>
                </div>
                <div class="form-group">
                    <label>Grade Hasil Tani</label>
                    <select class="form-control" name="grade" required>
                        <option value="A" ${val('grade') === 'A' ? 'selected' : ''}>Grade A (Premium)</option>
                        <option value="B" ${val('grade') === 'B' ? 'selected' : ''}>Grade B (Medium)</option>
                        <option value="C" ${val('grade') === 'C' ? 'selected' : ''}>Grade C (Rendah)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Masuk Gudang</label>
                    <input type="date" class="form-control" name="incoming_date" value="${val('incoming_date')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Detail Lokasi Gudang</label>
                    <input type="text" class="form-control" name="warehouse_location" placeholder="Silo Barat, Hangar 2" value="${val('warehouse_location')}" required>
                </div>
            `;
            
        case 'permodalan':
            return `
                <div class="form-group col-full">
                    <label>Sumber Dana / Modal</label>
                    <input type="text" class="form-control" name="source" placeholder="Contoh: Bantuan Pemerintah Desa" value="${val('source')}" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Nominal Modal (Rp)</label>
                    <input type="number" class="form-control" name="amount" value="${val('amount')}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Penerimaan</label>
                    <input type="date" class="form-control" name="date" value="${val('date')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Keterangan Tambahan</label>
                    <textarea class="form-control" name="description" rows="3" required>${val('description')}</textarea>
                </div>
            `;
            
        case 'pendanaan_sal':
            return `
                <div class="form-group col-full">
                    <label>Tujuan Alokasi Pendanaan SAL</label>
                    <input type="text" class="form-control" name="allocation_name" placeholder="Contoh: Pembelian Bibit" value="${val('allocation_name')}" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Pendanaan (Rp)</label>
                    <input type="number" class="form-control" name="amount" value="${val('amount')}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Penyaluran</label>
                    <input type="date" class="form-control" name="disbursement_date" value="${val('disbursement_date')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Status Penyaluran</label>
                    <select class="form-control" name="status" required>
                        <option value="Direncanakan" ${val('status') === 'Direncanakan' ? 'selected' : ''}>Direncanakan</option>
                        <option value="Disalurkan" ${val('status') === 'Disalurkan' ? 'selected' : ''}>Disalurkan</option>
                        <option value="Selesai" ${val('status') === 'Selesai' ? 'selected' : ''}>Selesai</option>
                    </select>
                </div>
            `;
            
        case 'jaminan_desa':
            return `
                <div class="form-group col-full">
                    <label>Nama Proyek Jaminan Dana Desa</label>
                    <input type="text" class="form-control" name="project_name" placeholder="Pembangunan Jalan Tani" value="${val('project_name')}" required>
                </div>
                <div class="form-group">
                    <label>Nominal Penjaminan (Rp)</label>
                    <input type="number" class="form-control" name="amount" value="${val('amount')}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Terbit Jaminan</label>
                    <input type="date" class="form-control" name="guarantee_date" value="${val('guarantee_date')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Status Jaminan</label>
                    <select class="form-control" name="status" required>
                        <option value="Aktif" ${val('status') === 'Aktif' ? 'selected' : ''}>Aktif</option>
                        <option value="Selesai" ${val('status') === 'Selesai' ? 'selected' : ''}>Selesai</option>
                        <option value="Batal" ${val('status') === 'Batal' ? 'selected' : ''}>Batal</option>
                    </select>
                </div>
            `;
            
        case 'simpan_pinjam':
            // Generate Member dropdown select
            let memberOptions = '';
            membersData.forEach(m => {
                const selected = data && parseInt(data.member_id) === m.id ? 'selected' : '';
                memberOptions += `<option value="${m.id}" ${selected}>${m.member_code} - ${m.name}</option>`;
            });
            
            const isLoan = val('type') === 'Pinjaman';
            
            return `
                <div class="form-group col-full">
                    <label>Pilih Anggota</label>
                    <select class="form-control" name="member_id" required>
                        <option value="" disabled selected>-- Pilih Anggota Koperasi --</option>
                        ${memberOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Transaksi</label>
                    <select class="form-control" name="type" required>
                        <option value="" disabled selected>-- Pilih Jenis --</option>
                        <option value="Simpanan Pokok" ${val('type') === 'Simpanan Pokok' ? 'selected' : ''}>Simpanan Pokok</option>
                        <option value="Simpanan Wajib" ${val('type') === 'Simpanan Wajib' ? 'selected' : ''}>Simpanan Wajib</option>
                        <option value="Simpanan Sukarela" ${val('type') === 'Simpanan Sukarela' ? 'selected' : ''}>Simpanan Sukarela</option>
                        <option value="Pinjaman" ${val('type') === 'Pinjaman' ? 'selected' : ''}>Pinjaman Kredit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nominal Dana (Rp)</label>
                    <input type="number" class="form-control" name="amount" value="${val('amount')}" required>
                </div>
                <div class="form-group loan-only-field" style="display: ${isLoan ? 'flex' : 'none'};">
                    <label>Bunga Pinjaman (% per bulan)</label>
                    <input type="number" step="0.1" class="form-control" name="interest_rate" value="${val('interest_rate') || 1.5}">
                </div>
                <div class="form-group loan-only-field" style="display: ${isLoan ? 'flex' : 'none'};">
                    <label>Tenor Jangka Waktu (Bulan)</label>
                    <input type="number" class="form-control" name="tenor" value="${val('tenor') || 12}">
                </div>
                <div class="form-group">
                    <label>Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="created_at" value="${val('created_at')}" required>
                </div>
                <div class="form-group">
                    <label>Status Transaksi</label>
                    <select class="form-control" name="status" required>
                        <option value="Aktif" ${val('status') === 'Aktif' ? 'selected' : ''}>Aktif/Valid</option>
                        <option value="Menunggu" ${val('status') === 'Menunggu' ? 'selected' : ''}>Menunggu Persetujuan</option>
                        <option value="Lunas" ${val('status') === 'Lunas' ? 'selected' : ''}>Lunas (Khusus Pinjaman)</option>
                    </select>
                </div>
            `;
            
        case 'info_desa':
            return `
                <div class="form-group col-full">
                    <label>Judul Informasi / Pengumuman</label>
                    <input type="text" class="form-control" name="title" value="${val('title')}" required>
                </div>
                <div class="form-group">
                    <label>Kategori Publikasi</label>
                    <select class="form-control" name="category" required>
                        <option value="Berita" ${val('category') === 'Berita' ? 'selected' : ''}>Berita Kegiatan</option>
                        <option value="Pengumuman" ${val('category') === 'Pengumuman' ? 'selected' : ''}>Pengumuman Penting</option>
                        <option value="Kegiatan" ${val('category') === 'Kegiatan' ? 'selected' : ''}>Agenda Kegiatan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Terbit</label>
                    <input type="date" class="form-control" name="published_at" value="${val('published_at')}" required>
                </div>
                <div class="form-group col-full">
                    <label>Isi Konten Publikasi</label>
                    <textarea class="form-control" name="content" rows="6" required>${val('content')}</textarea>
                </div>
            `;
            
        default:
            return '';
    }
}

// ==========================================
// 6. EDIT CONTROLLERS FOR ALL MODULES
// ==========================================
function editMember(data) {
    formId.value = data.id;
    formAction.value = 'edit_member';
    modalTitle.textContent = 'Edit Data Anggota';
    fieldsContainer.innerHTML = getFieldsHTML('keanggotaan', data);
    openModal();
}

function editRetail(data) {
    formId.value = data.id;
    formAction.value = 'edit_goods';
    modalTitle.textContent = 'Edit Data Barang Ritel';
    fieldsContainer.innerHTML = getFieldsHTML('ritel', data);
    openModal();
}

function editLogistics(data) {
    formId.value = data.id;
    formAction.value = 'edit_logistics';
    modalTitle.textContent = 'Edit Pelacakan Logistik';
    fieldsContainer.innerHTML = getFieldsHTML('logistik', data);
    openModal();
}

function editWarehouse(data) {
    formId.value = data.id;
    formAction.value = 'edit_warehouse';
    modalTitle.textContent = 'Edit Hasil Pertanian';
    fieldsContainer.innerHTML = getFieldsHTML('gudang', data);
    openModal();
}

function editCapital(data) {
    formId.value = data.id;
    formAction.value = 'edit_capital';
    modalTitle.textContent = 'Edit Sumber Modal';
    fieldsContainer.innerHTML = getFieldsHTML('permodalan', data);
    openModal();
}

function editSal(data) {
    formId.value = data.id;
    formAction.value = 'edit_sal';
    modalTitle.textContent = 'Edit Penyaluran SAL';
    fieldsContainer.innerHTML = getFieldsHTML('pendanaan_sal', data);
    openModal();
}

function editGuarantee(data) {
    formId.value = data.id;
    formAction.value = 'edit_guarantee';
    modalTitle.textContent = 'Edit Jaminan Dana Desa';
    fieldsContainer.innerHTML = getFieldsHTML('jaminan_desa', data);
    openModal();
}

function editSimpanPinjam(data) {
    formId.value = data.id;
    formAction.value = 'edit_simpan_pinjam';
    modalTitle.textContent = 'Edit Transaksi Simpan Pinjam';
    fieldsContainer.innerHTML = getFieldsHTML('simpan_pinjam', data);
    openModal();
}

function editInfo(data) {
    formId.value = data.id;
    formAction.value = 'edit_info';
    modalTitle.textContent = 'Edit Publikasi Informasi';
    fieldsContainer.innerHTML = getFieldsHTML('info_desa', data);
    openModal();
}

// ==========================================
// 7. AJAX FORM SUBMISSION & DELETIONS
// ==========================================
function submitForm(event) {
    event.preventDefault();
    const formData = new FormData(modalForm);
    
    fetch('api/handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan!',
            text: 'Terjadi kegagalan komunikasi dengan server.'
        });
    });
}

function deleteRecord(table, id) {
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: 'Data yang dihapus tidak dapat dipulihkan kembali!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C92A2A',
        cancelButtonColor: '#606770',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_record');
            formData.append('table', table);
            formData.append('id', id);
            
            fetch('api/handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Gagal menghubungi server untuk menghapus data.'
                });
            });
        }
    });
}

function approveLoan(id) {
    const formData = new FormData();
    formData.append('action', 'approve_loan');
    formData.append('id', id);
    
    fetch('api/handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Disetujui!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    });
}

function saveShuSettings(event) {
    event.preventDefault();
    const form = document.getElementById('form-shu');
    const formData = new FormData(form);
    
    fetch('api/handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Konfigurasi Disimpan!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    });
}

function auditStock(id, currentStock, name) {
    Swal.fire({
        title: `Audit Stok: ${name}`,
        input: 'number',
        inputLabel: 'Masukkan kuantitas stok baru',
        inputValue: currentStock,
        showCancelButton: true,
        confirmButtonText: 'Update Stok',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#C92A2A',
        cancelButtonColor: '#606770',
        inputValidator: (value) => {
            if (value === '' || isNaN(parseInt(value)) || parseInt(value) < 0) {
                return 'Harap masukkan jumlah stok yang valid (minimal 0)!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'quick_update_stock');
            formData.append('id', id);
            formData.append('stock', result.value);
            
            fetch('api/handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Gagal menghubungi server untuk mengupdate stok.'
                });
            });
        }
    });
}
