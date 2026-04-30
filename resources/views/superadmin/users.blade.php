@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="header-section" style="background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
        <div>
            <h1 class="page-title" style="margin: 0; font-weight: 800; letter-spacing: -0.03em;">User Accounts Management</h1>
            <p class="page-subtitle" style="margin-top: 0.5rem; opacity: 0.8;">Manage system access, roles, and user permissions.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary btn-lg" onclick="openAddModal()" style="padding: 0.8rem 1.5rem; font-weight: 700;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><path d="M12 5v14M5 12h14"/></svg>
                Add User Account
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-top: 1.5rem; background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 1rem; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="margin-top: 1.5rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="margin-top: 2rem; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>User Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Last Active</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td style="color: var(--text-muted);">{{ $loop->iteration }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="navbar-user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span style="font-weight: 600;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <span class="navbar-role-badge navbar-role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                                @if($user->is_suspended)
                                    <span style="font-size: 0.65rem; font-weight: 800; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 2px 8px; border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.2); text-transform: uppercase; letter-spacing: 0.05em; width: fit-content;">Suspended</span>
                                @endif
                            </div>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            {{ $user->updated_at->diffForHumans() }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn btn-icon" title="Edit User" 
                                    onclick="openEditModal({{ json_encode($user) }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                @if(Auth::id() !== $user->id)
                                    @if($user->is_suspended)
                                        <form action="{{ route('superadmin.users.unsuspend', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reactivate (unsuspend) this user account?')">
                                            @csrf
                                            <button type="submit" class="btn btn-icon" style="color: #10b981;" title="Unsuspend User">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-icon" style="color: #f59e0b;" title="Suspend User" 
                                            onclick="openSuspendModal({{ json_encode($user) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        </button>
                                    @endif
                                    
                                    <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Type DELETE to confirm removal of this user?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon" style="color: var(--danger);" title="Delete User">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="userModal" class="modal-overlay-premium">
    <div class="modal-content-premium">
        <div class="modal-header-premium">
            <h2 id="modalTitle">Add New User</h2>
            <button class="modal-close-premium" onclick="closeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="userForm" method="POST" style="padding: var(--spacing-lg);">
            @csrf
            <div id="methodField"></div>
            
            <div class="form-group-premium">
                <label>Full Name</label>
                <input type="text" name="name" id="userName" class="input-premium" required placeholder="User's full name">
            </div>

            <div class="form-group-premium">
                <label>Email Address</label>
                <input type="email" name="email" id="userEmail" class="input-premium" required placeholder="name@domain.com">
            </div>

            <div class="form-group-premium">
                <label id="passLabel">Password</label>
                <input type="password" name="password" id="userPass" class="input-premium" placeholder="Leave blank to keep current">
            </div>

            <div class="form-group-premium">
                <label>System Role</label>
                <select name="role" id="userRole" class="input-premium select-premium" required>
                    <option value="user">User (Viewer Only)</option>
                    <option value="admin">Administrator (Full Access)</option>
                    <option value="superadmin">Super Administrator (Owner)</option>
                </select>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()" style="padding: 0.6rem 1.5rem; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="padding: 0.6rem 2rem; font-weight: 700;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="modal-overlay-premium">
    <div class="modal-content-premium" style="max-width: 450px;">
        <div class="modal-header-premium">
            <h2 id="suspendModalTitle">Suspend Account</h2>
            <button class="modal-close-premium" onclick="closeSuspendModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="suspendForm" method="POST" style="padding: var(--spacing-lg);">
            @csrf
            <div class="form-group-premium">
                <label>Reason for Suspension</label>
                <textarea name="reason" id="suspensionReason" class="input-premium" required placeholder="Describe why this account is being suspended..." style="min-height: 100px; resize: vertical;"></textarea>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">This message will be recorded in the activity log and visible to other administrators.</p>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeSuspendModal()" style="padding: 0.6rem 1.5rem; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #f59e0b; border-color: #f59e0b; padding: 0.6rem 2rem; font-weight: 700;">Suspend Account</button>
            </div>
        </form>
    </div>
</div>

<style>
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-card);
    }
    .data-table th {
        text-align: left;
        padding: 1.25rem 1.5rem;
        background: rgba(255, 255, 255, 0.02);
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-subtle);
    }
    .data-table td {
        padding: 1.15rem 1.5rem;
        border-bottom: 1px solid var(--border-subtle);
        font-size: 0.95rem;
        vertical-align: middle;
    }
    .data-table tr:hover {
        background: rgba(255, 255, 255, 0.01);
    }
</style>

<script>
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const methodField = document.getElementById('methodField');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');

    function openAddModal() {
        modalTitle.innerText = "Add New User";
        submitBtn.innerText = "Create User Account";
        form.action = "{{ route('superadmin.users.store') }}";
        methodField.innerHTML = "";
        
        document.getElementById('userName').value = "";
        document.getElementById('userEmail').value = "";
        document.getElementById('userPass').value = "";
        document.getElementById('userPass').required = true;
        document.getElementById('passLabel').innerText = "Password";
        document.getElementById('userRole').value = "user";

        modal.style.display = 'flex';
    }

    function openEditModal(user) {
        modalTitle.innerText = "Edit User: " + user.name;
        submitBtn.innerText = "Save Changes";
        form.action = "/superadmin/users/" + user.id;
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('userName').value = user.name;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userPass').value = "";
        document.getElementById('userPass').required = false;
        document.getElementById('passLabel').innerText = "Password (Optional)";
        document.getElementById('userRole').value = user.role;

        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) closeModal();
        if (event.target == document.getElementById('suspendModal')) closeSuspendModal();
    }

    // Suspend Modal Logic
    function openSuspendModal(user) {
        document.getElementById('suspendModalTitle').innerText = "Suspend Account: " + user.name;
        document.getElementById('suspendForm').action = "/superadmin/users/" + user.id + "/suspend";
        document.getElementById('suspensionReason').value = "";
        document.getElementById('suspendModal').style.display = 'flex';
    }

    function closeSuspendModal() {
        document.getElementById('suspendModal').style.display = 'none';
    }
</script>
@endsection
