<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .user-avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4">User Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="userTableBody">
                    <tr>
                        <td colspan="6" class="text-center">Loading...</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" enctype="multipart/form-data">
                    <input type="hidden" id="userId" name="user_id">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        <div class="invalid-feedback"></div>
                        <img id="imagePreview" class="preview-image d-none" src="" alt="Preview">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    $(document).ready(function() {
        loadUsers();
    });

    function loadUsers() {
        $.ajax({
            url: "{{ route('users.list') }}",
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayUsers(response.data);
                }
            },
            error: function() {

            }
        });
    }
    function displayUsers(users) {
        let html = '';
        if (users.length === 0) {
            html = '<tr><td colspan="6" class="text-center">No users found</td></tr>';
        } else {
            users.forEach(function(user) {
                html += `
                        <tr>
                            <td>${user.id}</td>
                            <td><img src="${user.image}" class="user-avatar" alt="${user.name}"></td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.phone || 'N/A'}</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editUser(${user.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
            });
        }
        $('#userTableBody').html(html);
    }
    function openCreateModal() {
        $('#modalTitle').text('Add User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#imagePreview').addClass('d-none');
        $('.form-control').removeClass('is-invalid');
    }
    function editUser(id) {
        let url = '{{route('users.update',":id")}}';
        $.ajax({
            url: url.replace(':id',id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#modalTitle').text('Edit User');
                    $('#userId').val(response.data.id);
                    $('#name').val(response.data.name);
                    $('#email').val(response.data.email);
                    $('#phone').val(response.data.phone);

                    if (response.data.profile_image) {
                        $('#imagePreview').attr('src', response.data.image_url).removeClass('d-none');
                    }

                    $('#userModal').modal('show');
                }
            },
            error: function() {
            }
        });
    }

    function saveUser() {
        let formData = new FormData($('#userForm')[0]);
        formData.append('_token', "{{ csrf_token() }}");

        let userId = $('#userId').val();
        url = '{{route('users.update',":id")}}';
        let url = userId ? url.replace(':id',userId) : "{{ route('users.store') }}";

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#userModal').modal('hide');
                    toastr.success(response.message);
                    loadUsers();
                }
            },
            error: function(xhr) {
            }
        });
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = '{{route('users.destroy',":id")}}';
                $.ajax({
                    url: url.replace(':id',id),
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadUsers();
                        }
                    },
                    error: function() {

                    }
                });
            }
        });
    }

    $('#profile_image').change(function() {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>
</body>
</html>
