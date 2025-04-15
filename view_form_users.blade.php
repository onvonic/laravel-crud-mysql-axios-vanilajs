<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INPUT</title>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-4 ">
            <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex flex-stack ">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">DATA USERS ( <span id="paket_count"></span> Paket)</span><br>
                    <span class="text-muted mt-1 fw-semibold fs-7">Create, edit, and manage user data on this page </span>
                </h3>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <div style="display: flex;gap: 5px; align-items: center;justify-content: space-between;padding: 5px 10px; flex-wrap: wrap;">
                        <div style="display: flex; gap: 0.5rem;">
                            <select class="form-control form-control-xs" style="font-size: 0.9rem; padding:0.1rem 1rem; width: auto;" id="short_by_limit">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="75">75</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <select class="form-control form-control-xs" style="font-size: 0.9rem; padding:0.1rem 1rem" id="short_by_roles">
                                <option value="">Filter By Roles</option>
                                <option value="developer">Developer</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin">Admin</option>
                            </select>
                            <select class="form-control form-control-xs" style="font-size: 0.9rem; padding:0.1rem 1rem" id="short_by_status">
                                <option value="">Filter By Status</option>
                                <option value="active">Active</option>
                                <option value="notactive">Not Active</option>
                            </select>
                            <input type="text" class="form-control form-control-xs" id="short_by_search" placeholder="search..." />
                            <button id="btn_show_component_modal_form_input" class="btn btn-primary btn-xs" style="width: 100%"><i class="las la-plus fs-1"></i> ADD USERS </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle" id="datatable">
                                <thead class="thead-light">
                                    <tr class="fw-bold bg-light">
                                        <th class="ps-4">Users</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Desc Login</th>
                                        <th>Desc</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="data_table">
    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="component_modal_form_input" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myLargeModalLabel">NEW USERS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control form-control-sm" id="input_name" />
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control form-control-sm" id="input_email" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Password</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-sm" id="input_password" autocomplete="new-password">
                                    <button class="btn btn-light shadow-none ms-0 toggle-password" data-input="input_password" type="button">
                                        <i class="las la-eye fs-3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Roles</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm" id="input_roles"></select>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Photo</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control form-control-sm" id="input_photo">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium" data-bs-dismiss="modal"><i class="ri-close-line me-1 align-middle"></i> Close</a>
                        <button type="button" id="button_insert" class="btn btn-primary ">Save changes</button>
                        <button type="button" id="button_insert_send" class="btn btn-sm btn-primary" style="display: none">
                            <span class="spinner-border" style="--bs-spinner-width: 10px; --bs-spinner-height: 10px;" role="status"></span>
                            <span>loading ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="component_modal_form_edit" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myLargeModalLabel">UPDATE USERS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Name</label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control form-control-sm" id="edit_id" />
                                <input type="text" class="form-control form-control-sm" id="edit_name" />
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control form-control-sm" id="edit_email" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Password</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-sm" id="edit_password" autocomplete="new-password">
                                    <button class="btn btn-light shadow-none ms-0 toggle-password" data-input="edit_password" type="button">
                                        <i class="las la-eye fs-3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Roles</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm" id="edit_roles"></select>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Photo</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control form-control-sm" id="edit_photo">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium" data-bs-dismiss="modal"><i class="ri-close-line me-1 align-middle"></i> Close</a>
                        <button type="button" id="button_update" class="btn btn-primary ">Save changes</button>
                        <button type="button" id="button_update_send" class="btn btn-sm btn-primary" style="display: none">
                            <span class="spinner-border" style="--bs-spinner-width: 10px; --bs-spinner-height: 10px;" role="status"></span>
                            <span>loading ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- VARIABLE --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        // Variabel untuk filter/search
        const sySearch = document.getElementById('short_by_search');
        const syLimit = document.getElementById('short_by_limit');
        const syRoles = document.getElementById('short_by_roles');
        const syStatus = document.getElementById('short_by_status');
    
        // Variabel untuk form input
        const inName = document.getElementById('input_name');
        const inEmail = document.getElementById('input_email');
        const inPassword = document.getElementById('input_password');
        const inRoles = document.getElementById('input_roles');
        const inPhoto = document.getElementById('input_photo');
    
        // Variabel untuk form edit
        const edId = document.getElementById('edit_id');
        const edName = document.getElementById('edit_name');
        const edEmail = document.getElementById('edit_email');
        const edPassword = document.getElementById('edit_password');
        const edRoles = document.getElementById('edit_roles');
        const edPhoto = document.getElementById('edit_photo');
    
        // Variabel untuk button
        const buttonInsert = document.getElementById('button_insert');
        const buttonInsertSend = document.getElementById('button_insert_send');
        const buttonUpdate = document.getElementById('button_update');
        const buttonUpdateSend = document.getElementById('button_update_send');
        const buttonShowModalFormInput = document.getElementById('btn_show_component_modal_form_input');
        // Variabel untuk modal
        const componentModalFormInput = document.getElementById('component_modal_form_input');
        const componentModalFormEdit = document.getElementById('component_modal_form_edit');
        // Variabel table data
        const dataTable = document.getElementById('data_table');
    
        const getDeleteButton = (target) => target.closest('.btn_delete');
        const getShowModalEditButton = (target) => target.closest('.btn_show_component_modal_form_edit');
    </script>
    {{-- ========================================================================================================================================================= --}}
    {{-- ALERT --}}
    {{-- ========================================================================================================================================================= --}}
    <script>
        // FUNGSI ALERT SUCCESS
        function alertResponseSuccess(message) {
            Swal.fire(
                'Success!',
                `<div style="color: #50cd89;">${message}</div>`,
                'success'
            );
        }
        // FUNGSI ALERT ERROR
        function alertResponseError(response) {
            let errorMessage = `<div style="color: #000;">${response.message}</div><br>`;
            if (response.error) {
                if (typeof response.error === 'object' && !Array.isArray(response.error)) {
                    for (const [key, value] of Object.entries(response.error)) {
                        if (Array.isArray(value)) {
                            errorMessage +=
                                `<strong>${key}:</strong><ul style="list-style-type: none; padding-left: 0;">`;
                            value.forEach(error => {
                                errorMessage += `<li>${error}</li>`;
                            });
                            errorMessage += `</ul>`;
                        }
                    }
                } else {
                    errorMessage += `${response.error}`;
                }
            }
            Swal.fire(
                'Error!',
                `<div style="color: #f00;">${errorMessage}</div>`,
                'error'
            );
        }
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- SET DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
    // Data roles
    async function getUserRoles() {
        try {
            const response = await axios.get(`{{ route('api.users.roles') }}`);
            let dataroles = response.data.data;
            // Handle edit_roles select
            function setEditRoles() {
                if (edRoles) {
                    edRoles.innerHTML = '<option value="">Select Roles</option>';
                    dataroles.forEach(role => {
                        edRoles.innerHTML += `<option value="${role.name_roles}">${role.name_roles}</option>`;
                    });
                }
            }
            // Handle input_roles select
            function setInputRoles() {
                if (inRoles) {
                    inRoles.innerHTML = '<option value="">Select Roles</option>';
                    dataroles.forEach(role => {
                        inRoles.innerHTML += `<option value="${role.name_roles}">${role.name_roles}</option>`;
                    });
                }
            }
            // Handle short_by_roles select
            function setShortByRoles() {
                if (syRoles) {
                    syRoles.innerHTML = '<option value="">All Roles</option>';
                    dataroles.forEach(role => {
                        syRoles.innerHTML += `<option value="${role.name_roles}">${role.name_roles}</option>`;
                    });
                }
            }
            // Call all the functions
            setEditRoles();
            setInputRoles();
            setShortByRoles();
        } catch (error) {
            console.error('Error fetching users roles:', error);
        }
    }
    // Call when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        getUserRoles();
    });
</script>
    <script>
        // Data action_name 
        const rolesName = [
            { value: 'admin', label: 'Admin' },
            { value: 'owner', label: 'Owner' },
            { value: 'dev', label: 'Dev' },
        ];
        // Handle setShortByRoles select
        function setShortByRoles() {
            if (syRoles) {
                syRoles.innerHTML = '<option value="">Select Action</option>';
                rolesName.forEach(role => {
                    syRoles.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Handle input_action_name select
        function setInputActionName() {
            if (inRoles) {
                inRoles.innerHTML = '<option value="">Select Action</option>';
                rolesName.forEach(role => {
                    inRoles.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Handle input_action_name select
        function setEditActionName() {
            if (edRoles) {
                edRoles.innerHTML = '<option value="">Select Action</option>';
                rolesName.forEach(role => {
                    edRoles.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Jalankan semua fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            setShortByRoles();
            setInputActionName();
            setEditActionName();
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- GET DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        async function fetchData(syVSearch, syVLimit, syVRoles, syVStatus) {
            try {
                let url = `{{ route('app.users.data') }}`;
                let params = new URLSearchParams();
    
                if (syVSearch && syVSearch.trim() !== '') {
                    params.append('search', syVSearch.trim());
                }
                if (syVLimit != undefined && syVLimit != null && syVLimit != '') {
                    params.append('limit', syVLimit);
                }
                if (syVRoles && syVRoles.trim() !== '') {
                    params.append('roles', syVRoles.trim());
                }
                if (syVStatus && syVStatus.trim() !== '') {
                    params.append('status', syVStatus.trim());
                }
                // Hanya tambahkan parameter ke URL jika ada parameter
                if (params.toString()) {
                    url += '?' + params.toString();
                }
                const response = await axios.get(url);
                const data = response.data.data;
    
                dataTable.innerHTML = '';
                data.forEach(item => {
                    dataTable.innerHTML += `
                        <tr>
                            <td class="ps-4">${item.name}</td>
                            <td>${item.email}</td>
                            <td>${item.roles?item.roles:''}</td>
                            <td>
                                <div class="form-check form-switch" dir="ltr">
                                    <input type="checkbox" class="form-check-input status_account" data-id="${item.id}" data-status="${item.status}" ${item.attributes_status}>
                                    <label class="form-check-label">${item.status}</label>
                                </div>    
                            </td>
                            <td>${item.last_login?item.last_login:''}</td>
                            <td>${item.last_login_relative?item.last_login_relative:''}</td>
                            <td>${item.password_updated?item.password_updated:''}</td>
    
                            <td>
                                <a href="#" data-id="${item.id}" class="btn_show_component_modal_form_edit"><i class="fa-regular fa-pen-to-square text-success"></i></a>
                                <a href="#" data-id="${item.id}" class="btn_delete"><i class="fa-solid fa-trash text-danger"></i></a>
                            </td>
                        </tr>
                    `;
                });
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const syVSearch = sySearch.value;
            const syVLimit = syLimit.value;
            const syVRoles = syRoles.value;
            const syVStatus = syStatus.value;
            fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
        });
        // GETDATA BY SEARCH
        sySearch.addEventListener('keyup', (event) => {
            const syVSearch = event.target.value.trim();
            const syVLimit = syLimit.value;
            const syVRoles = syRoles.value;
            const syVStatus = syStatus.value;
            fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
        });
        // GETDATA BY LIMIT
        syLimit.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = event.target.value.trim();
            const syVRoles = syRoles.value;
            const syVStatus = syStatus.value;
            fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
        });
        // GETDATA BY MODULE
        syRoles.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = syLimit.value;
            const syVRoles = event.target.value.trim();
            const syVStatus = syStatus.value;
            fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
        });
        // GETDATA BY ACTION
        syStatus.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = syLimit.value;
            const syVRoles = syRoles.value;
            const syVStatus = event.target.value.trim();
            fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- INSERT DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        buttonShowModalFormInput.addEventListener('click', function() {
            new bootstrap.Modal(componentModalFormInput).show();
        });
        buttonInsert.addEventListener('click', async function() {
            buttonInsert.style.display = 'none';
            buttonInsertSend.style.display = 'inline-block';
            try {
                const formData = new FormData();
                formData.append('name', inName.value);
                formData.append('email', inEmail.value);
                formData.append('password', inPassword.value);
                formData.append('roles', inRoles.value);
                formData.append('photo', inPhoto.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
    
                const responseData = await axios.post(`{{ route('app.users.insert') }}`, formData);
                let response = responseData.data;
                if (response.status === true) {
                    alertResponseSuccess(response.message);
                    // refresh fetchData
                    const syVSearch = sySearch.value;
                    const syVLimit = syLimit.value;
                    const syVRoles = syRoles.value;
                    const syVStatus = syStatus.value;
                    fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
                    // Clear the form fields
                    inName.value = '';
                    inEmail.value = '';
                    inPassword.value = '';
                    inRoles.value = '';
                    inPhoto.value = '';
                    // Close the modal
                    bootstrap.Modal.getInstance(componentModalFormInput).hide();
                } else {
                    alertResponseError(response);
                }
            } catch (error) {
                console.error('Error saving data:', error);
            } finally {
                buttonInsert.style.display = 'inline-block';
                buttonInsertSend.style.display = 'none';
            }
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- UPDATE DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        document.addEventListener('click', async function(event) {
            const buttonShowModalEdit = getShowModalEditButton(event.target);
            if (buttonShowModalEdit) {
                event.preventDefault();
                const id = buttonShowModalEdit.dataset.id;
                let url = `{{ route('app.users.data') }}`;
                let params = new URLSearchParams();
                params.append('id', id);
                // Hanya tambahkan parameter ke URL jika ada parameter
                if (params.toString()) {
                    url += '?' + params.toString();
                }
                try {
                    const response = await axios.get(url);
                    const data = response.data.data;
                    edId.value = data.id;
                    edName.value = data.name;
                    edEmail.value = data.email;
                    edRoles.value = data.roles;
                    new bootstrap.Modal(componentModalFormEdit).show();
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }
        });
        buttonUpdate.addEventListener('click', async function() {
            buttonUpdate.style.display = 'none';
            buttonUpdateSend.style.display = 'inline-block';
            try {
                const formData = new FormData();
                formData.append('id', edId.value);
                formData.append('name', edName.value);
                formData.append('email', edEmail.value);
                formData.append('password', edPassword.value);
                formData.append('roles', edRoles.value);
                formData.append('photo', edPhoto.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
    
                const responseData = await axios.post(`{{ route('app.users.update') }}`, formData);
                let response = responseData.data;
                if (response.status === true) {
                    alertResponseSuccess(response.message);
                    // refresh fetchData
                    const syVSearch = sySearch.value;
                    const syVLimit = syLimit.value;
                    const syVRoles = syRoles.value;
                    const syVStatus = syStatus.value;
                    fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
                    // Close the modal
                    bootstrap.Modal.getInstance(componentModalFormEdit).hide();
                } else {
                    alertResponseError(response);
                }
            } catch (error) {
                console.error('Error saving data:', error);
            } finally {
                buttonUpdate.style.display = 'inline-block';
                buttonUpdateSend.style.display = 'none';
            }
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- DELETE DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        document.addEventListener('click', async function(event) {
            const buttonDelete = getDeleteButton(event.target);
            if (buttonDelete) {
                event.preventDefault();
                const id = buttonDelete.dataset.id;
                Swal.fire({
                    title: 'Apakah kamu yakin?',
                    text: "Deleted data cannot be recovered",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(async (result) => {
                    if (result.value) {
                        try {
                            const responseData = await axios.post(`{{ route('app.users.delete') }}`, {
                                id: id,
                            });
                            let response = responseData.data;
                            if (response.status === true) {
                                alertResponseSuccess(response.message);
                                const syVSearch = sySearch.value;
                                const syVLimit = syLimit.value;
                                const syVRoles = syRoles.value;
                                const syVStatus = syStatus.value;
                                fetchData(syVSearch, syVLimit, syVRoles, syVStatus);
                            } else {
                                alertResponseError(response);
                            }
                        } catch (error) {
                            console.error("Error deleting data:", error);
                            alertResponseError("An error occurred while deleting the data.");
                        }
                    }
                });
            }
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- HANDLE STATUS ACTIVE/NONACTIVE USER --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('change', async function(event) {
                if (!event.target.matches('.status_account')) return;
                const checkbox = event.target;
                const id = checkbox.dataset.id;
                const newStatus = checkbox.checked ? 'active' : 'nonactive';
    
                const responseData = await axios.post("{{ route('app.users.update.status') }}", {
                    id: id,
                    status: newStatus
                });
                let response = responseData.data;
                if (response.status === true) {
                    // refresh fetchData
                    const syVSearch = sySearch.value;
                    const syVLimit = syLimit.value;
                    const syVRoles = syRoles.value;
                    const syVStatus = syStatus.value;
                    fetchData(syVSearch, syVLimit, syVStatus, syVRoles);
                    alertResponseSuccess(response.message);
                } else {
                    alertResponseError(response.message);
                }
            });
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- TOGGLE PASSWORD VISIBILITY --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const inputId = this.getAttribute('data-input');
                const inputField = document.getElementById(inputId);
                if (inputField) {
                    inputField.type = inputField.type === 'password' ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('la-eye');
                        icon.classList.toggle('la-eye-slash');
                    }
                }
            });
        });
    </script>
</body>

</html>
