<?php
$title = 'Admin Dashboard';
?>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i>
            <span>Admin Panel</span>
        </div>
        <nav class="sidebar-nav">
            <a href="<?php echo url('home'); ?>" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="<?php echo url('admin'); ?>" class="nav-item active">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
            <a href="<?php echo url('maintenance'); ?>" class="nav-item">
                <i class="fas fa-tools"></i>
                <span>Maintenance</span>
            </a>
            <a href="<?php echo url('logout'); ?>" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="muted">Logged in as</div>
            <div class="user-name"><?php echo e($user['name']); ?> <?php echo e($user['surname']); ?></div>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div class="header-content">
                <h1>User Management</h1>
                <p class="muted">Manage users, permissions, and access levels</p>
            </div>
            <div class="status-pill <?php echo $maintenance ? 'error' : 'success'; ?>">
                <i class="fas fa-circle"></i>
                <span><?php echo $maintenance ? 'Maintenance Mode' : 'Online'; ?></span>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count($pendingUsers); ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
        </div>

        <section class="card glass">
            <div class="card-header">
                <div>
                    <h3>All Users</h3>
                    <span class="muted"><?php echo count($users); ?> total users</span>
                </div>
                <button class="btn btn-primary" onclick="openCreateUserModal()">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Access</th>
                        <th>Devices</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr data-user-id="<?php echo e($u['id']); ?>">
                            <td><?php echo e($u['id']); ?></td>
                            <td>
                                <div class="user-info">
                                    <strong><?php echo e($u['name']); ?> <?php echo e($u['surname']); ?></strong>
                                    <?php if ($u['phone']): ?>
                                        <span class="muted"><?php echo e($u['phone']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo e($u['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $u['role'] === 'admin' ? 'primary' : 'default'; ?>">
                                    <?php echo e($u['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $u['is_active'] ? 'success' : 'warning'; ?>">
                                    <?php echo $u['is_active'] ? 'Active' : 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="access-badges">
                                    <span class="access-badge <?php echo $u['access_b1'] ? 'active' : ''; ?>" title="B1 Access">B1</span>
                                    <span class="access-badge <?php echo $u['access_b2'] ? 'active' : ''; ?>" title="B2 Access">B2</span>
                                </div>
                            </td>
                            <td><?php echo e($u['device_limit']); ?></td>
                            <td class="text-right">
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" onclick="editUser(<?php echo e($u['id']); ?>)" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($u['id'] != $user['id']): ?>
                                        <button class="btn-icon btn-delete" onclick="deleteUser(<?php echo e($u['id']); ?>, '<?php echo e($u['name'] . ' ' . $u['surname']); ?>')" title="Delete User">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if (!empty($pendingUsers)): ?>
        <section class="card glass" style="margin-top: 24px;">
            <div class="card-header">
                <div>
                    <h3>Pending Approvals</h3>
                    <span class="muted"><?php echo count($pendingUsers); ?> users awaiting approval</span>
                </div>
            </div>
            <div class="card-body">
                <div class="pending-list">
                    <?php foreach ($pendingUsers as $u): ?>
                        <div class="pending-item">
                            <div class="pending-info">
                                <strong><?php echo e($u['name']); ?> <?php echo e($u['surname']); ?></strong>
                                <span class="muted"><?php echo e($u['email']); ?></span>
                                <span class="muted">Registered: <?php echo date('M d, Y', strtotime($u['created_at'])); ?></span>
                            </div>
                            <div class="pending-actions">
                                <button class="btn btn-sm btn-success" onclick="approveUser(<?php echo e($u['id']); ?>)">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo e($u['id']); ?>, '<?php echo e($u['name'] . ' ' . $u['surname']); ?>')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editUserForm" class="modal-body">
            <input type="hidden" id="editUserId" name="user_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="editName">First Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editSurname">Last Name</label>
                    <input type="text" id="editSurname" name="surname" required>
                </div>
            </div>

            <div class="form-group">
                <label for="editEmail">Email</label>
                <input type="email" id="editEmail" name="email" required>
            </div>

            <div class="form-group">
                <label for="editPhone">Phone (Optional)</label>
                <input type="text" id="editPhone" name="phone">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editRole">Role</label>
                    <select id="editRole" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editDeviceLimit">Device Limit</label>
                    <input type="number" id="editDeviceLimit" name="device_limit" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="editIsActive" name="is_active" value="1">
                    Active Account
                </label>
            </div>

            <div class="form-group">
                <label>Access Levels</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" id="editAccessB1" name="access_b1" value="1">
                        B1 Access
                    </label>
                    <label>
                        <input type="checkbox" id="editAccessB2" name="access_b2" value="1">
                        B2 Access
                    </label>
                </div>
            </div>

            <div id="editUserMessage" class="form-message"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New User</h2>
            <button class="modal-close" onclick="closeCreateUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createUserForm" class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="createName">First Name <span class="required">*</span></label>
                    <input type="text" id="createName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="createSurname">Last Name <span class="required">*</span></label>
                    <input type="text" id="createSurname" name="surname" required>
                </div>
            </div>

            <div class="form-group">
                <label for="createEmail">Email <span class="required">*</span></label>
                <input type="email" id="createEmail" name="email" required>
            </div>

            <div class="form-group">
                <label for="createPassword">Password <span class="required">*</span></label>
                <input type="password" id="createPassword" name="password" required minlength="8">
                <small class="form-hint">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="createPhone">Phone (Optional)</label>
                <input type="text" id="createPhone" name="phone">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="createRole">Role <span class="required">*</span></label>
                    <select id="createRole" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="createDeviceLimit">Device Limit <span class="required">*</span></label>
                    <input type="number" id="createDeviceLimit" name="device_limit" min="1" value="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="createIsActive" name="is_active" value="1" checked>
                    Active Account (user can login immediately)
                </label>
            </div>

            <div class="form-group">
                <label>Access Levels</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" id="createAccessB1" name="access_b1" value="1">
                        B1 Access
                    </label>
                    <label>
                        <input type="checkbox" id="createAccessB2" name="access_b2" value="1">
                        B2 Access
                    </label>
                </div>
            </div>

            <div id="createUserMessage" class="form-message"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New User</h2>
            <button class="modal-close" onclick="closeCreateUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createUserForm" class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="createName">First Name <span class="required">*</span></label>
                    <input type="text" id="createName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="createSurname">Last Name <span class="required">*</span></label>
                    <input type="text" id="createSurname" name="surname" required>
                </div>
            </div>

            <div class="form-group">
                <label for="createEmail">Email <span class="required">*</span></label>
                <input type="email" id="createEmail" name="email" required>
            </div>

            <div class="form-group">
                <label for="createPassword">Password <span class="required">*</span></label>
                <input type="password" id="createPassword" name="password" required minlength="8">
                <small class="form-hint">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="createPhone">Phone (Optional)</label>
                <input type="text" id="createPhone" name="phone">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="createRole">Role <span class="required">*</span></label>
                    <select id="createRole" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="createDeviceLimit">Device Limit <span class="required">*</span></label>
                    <input type="number" id="createDeviceLimit" name="device_limit" min="1" value="1" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="createIsActive" name="is_active" value="1" checked>
                    Active Account (user can login immediately)
                </label>
            </div>

            <div class="form-group">
                <label>Access Levels</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" id="createAccessB1" name="access_b1" value="1">
                        B1 Access
                    </label>
                    <label>
                        <input type="checkbox" id="createAccessB2" name="access_b2" value="1">
                        B2 Access
                    </label>
                </div>
            </div>

            <div id="createUserMessage" class="form-message"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentUserId = null;

async function editUser(userId) {
    currentUserId = userId;
    const modal = document.getElementById('editUserModal');
    const form = document.getElementById('editUserForm');
    const message = document.getElementById('editUserMessage');
    
    message.textContent = '';
    message.className = 'form-message';
    
    try {
        const res = await fetch(`<?php echo url('api/admin/users/'); ?>${userId}`);
        const data = await res.json();
        
        if (!data.success) {
            alert('Failed to load user data');
            return;
        }
        
        const user = data.data;
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editName').value = user.name;
        document.getElementById('editSurname').value = user.surname;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editPhone').value = user.phone || '';
        document.getElementById('editRole').value = user.role;
        document.getElementById('editDeviceLimit').value = user.device_limit;
        document.getElementById('editIsActive').checked = user.is_active == 1;
        document.getElementById('editAccessB1').checked = user.access_b1 == 1;
        document.getElementById('editAccessB2').checked = user.access_b2 == 1;
        
        modal.style.display = 'flex';
    } catch (err) {
        alert('Error loading user data');
    }
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('editUserForm').reset();
    document.getElementById('editUserMessage').textContent = '';
}

document.getElementById('editUserForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = document.getElementById('editUserMessage');
    message.textContent = '';
    message.className = 'form-message';
    
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        surname: formData.get('surname'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        role: formData.get('role'),
        device_limit: parseInt(formData.get('device_limit')),
        is_active: formData.get('is_active') ? 1 : 0,
        access_b1: formData.get('access_b1') ? 1 : 0,
        access_b2: formData.get('access_b2') ? 1 : 0
    };
    
    try {
        const res = await fetch(`<?php echo url('api/admin/users/'); ?>${currentUserId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (!res.ok || !result.success) {
            message.textContent = result.message || 'Update failed';
            message.classList.add('error');
            return;
        }
        
        message.textContent = result.message || 'User updated successfully';
        message.classList.add('success');
        
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch (err) {
        message.textContent = 'Network error. Please try again.';
        message.classList.add('error');
    }
});

async function deleteUser(userId, userName) {
    if (!confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        return;
    }
    
    try {
        const res = await fetch(`<?php echo url('api/admin/users/'); ?>${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await res.json();
        
        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to delete user');
            return;
        }
        
        alert('User deleted successfully');
        window.location.reload();
    } catch (err) {
        alert('Network error. Please try again.');
    }
}

async function approveUser(userId) {
    try {
        const res = await fetch(`<?php echo url('api/admin/users/'); ?>${userId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await res.json();
        
        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to approve user');
            return;
        }
        
        alert('User approved successfully');
        window.location.reload();
    } catch (err) {
        alert('Network error. Please try again.');
    }
}

// Create User Functions
function openCreateUserModal() {
    const modal = document.getElementById('createUserModal');
    const form = document.getElementById('createUserForm');
    const message = document.getElementById('createUserMessage');
    
    form.reset();
    message.textContent = '';
    message.className = 'form-message';
    
    // Set default values
    document.getElementById('createIsActive').checked = true;
    document.getElementById('createDeviceLimit').value = 1;
    document.getElementById('createRole').value = 'user';
    
    modal.style.display = 'flex';
}

function closeCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'none';
    document.getElementById('createUserForm').reset();
    document.getElementById('createUserMessage').textContent = '';
}

document.getElementById('createUserForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = document.getElementById('createUserMessage');
    message.textContent = '';
    message.className = 'form-message';
    
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        surname: formData.get('surname'),
        email: formData.get('email'),
        password: formData.get('password'),
        phone: formData.get('phone') || null,
        role: formData.get('role'),
        device_limit: parseInt(formData.get('device_limit')),
        is_active: formData.get('is_active') ? 1 : 0,
        access_b1: formData.get('access_b1') ? 1 : 0,
        access_b2: formData.get('access_b2') ? 1 : 0
    };
    
    try {
        const res = await fetch('<?php echo url('api/admin/users'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (!res.ok || !result.success) {
            message.textContent = result.message || 'Failed to create user';
            message.classList.add('error');
            if (result.errors) {
                const errorList = Object.values(result.errors).join(', ');
                message.textContent += ': ' + errorList;
            }
            return;
        }
        
        message.textContent = result.message || 'User created successfully';
        message.classList.add('success');
        
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch (err) {
        message.textContent = 'Network error. Please try again.';
        message.classList.add('error');
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editUserModal');
    const createModal = document.getElementById('createUserModal');
    
    if (event.target == editModal) {
        closeEditModal();
    }
    if (event.target == createModal) {
        closeCreateUserModal();
    }
}
</script>
