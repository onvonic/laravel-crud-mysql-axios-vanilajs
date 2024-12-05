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
                    <span class="card-label fw-bold fs-3 mb-1">DATA USERS PERMISSION</span><br>
                    <span class="text-muted mt-1 fw-semibold fs-7">Create, edit, and manage users permission action data on this page </span>
                </h3>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <div
                        style="display: flex;gap: 5px; align-items: center;justify-content: space-between;padding: 5px 10px; flex-wrap: wrap;">
                        <div style="display: flex; gap: 0.5rem;">
                            <select class="form-control form-control-xs"
                                style="font-size: 0.9rem; padding:0.1rem 1rem; width: auto;" id="short_by_limit">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="75">75</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <select class="form-control form-control-xs" style="font-size: 0.9rem; padding:0.1rem 1rem; width: auto;" id="short_by_module"></select>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <select class="form-control form-control-xs" style="font-size: 0.9rem; padding:0.1rem 1rem; width: auto;" id="short_by_action"></select>
                            </div>
                            <input type="text" class="form-control form-control-xs" id="short_by_search" placeholder="search..." />
                            <button id="btn_show_component_modal_form_input" class="btn btn-primary btn-xs" style="width: 100%"><i class="las la-plus fs-1"></i> ADD MOD ACTION</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle" id="datatable">
                                <thead class="thead-light"> 
                                    <tr class="fw-bold bg-light">
                                        <th class="ps-4">ID</th>
                                        <th>Modules</th>
                                        <th>Action</th>
                                        <th>Created at</th>
                                        <th>Updated at</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="data_table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="component_modal_form_edit" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myLargeModalLabel">UPDATE MODULES ACTION</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Module name</label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control form-control-sm" id="edit_id" required />
                                <select class="form-control form-control-sm" id="edit_module_id"></select>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">module
                                action</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm" id="edit_action_name"></select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium" data-bs-dismiss="modal"><i class="ri-close-line me-1 align-middle"></i> Close</a>
                        <button type="button" id="button_update" class="btn btn-primary ">Save changes</button>
                        <button type="button" id="button_update_send" class="btn btn-sm btn-primary" style="display: none">
                            <span class="spinner-border" style="--bs-spinner-width: 10px; --bs-spinner-height: 10px;"
                                role="status"></span>
                            <span>loading ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
    {{-- VARIABLE --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        // Variabel untuk filter/search
        const sySearch = document.getElementById('short_by_search');
        const syLimit = document.getElementById('short_by_limit');
        const syModule = document.getElementById('short_by_module');
        const syAction = document.getElementById('short_by_action');

        // Variabel untuk form edit
        const edId = document.getElementById('edit_id');
        const edModuleId = document.getElementById('edit_module_id');
        const edActionName = document.getElementById('edit_action_name');

        // Variabel untuk button
        const buttonUpdate = document.getElementById('button_update');
        const buttonUpdateSend = document.getElementById('button_update_send');
        // Variabel untuk modal
        const componentModalFormEdit = document.getElementById('component_modal_form_edit');
        // Variabel table data
        const dataTable = document.getElementById('data_table');

        const getShowModalEditButton = (target) => target.closest('.btn_show_component_modal_form_edit');
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- SET DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        // Data action_name 
        const actionName = [
            { value: 'view', label: 'view'},
            { value: 'insert', label: 'insert'},
            { value: 'delete', label: 'delete'},
            { value: 'update', label: 'update'},
            { value: 'verifikasi', label: 'verifikasi'},
            { value: 'validasi', label: 'validasi'}
        ];
        // Handle input_action_name select
        function setInputActionName() {
            if (inActionName) {
                inActionName.innerHTML = '<option value="">Select Action</option>';
                actionName.forEach(role => {
                    inActionName.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Handle edit_action_name select
        function setEditActionName() {
            if (edActionName) {
                edActionName.innerHTML = '<option value="">Select Action</option>';
                actionName.forEach(role => {
                    edActionName.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Handle short_by_action_name select
        function setShortByActionName() {
            if (syAction) {
                syAction.innerHTML = '<option value="">All Action</option>';
                actionName.forEach(role => {
                    syAction.innerHTML += `<option value="${role.value}">${role.label}</option>`;
                });
            }
        }
        // Jalankan semua fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function () {
            setEditActionName();
            setInputActionName();
            setShortByActionName();
        });
    </script>
    <script>
        async function fetchModules() {
            const response = await axios.get(`{{ route('app.users.modules.data') }}`);
            const modules = response.data.data.map(module => ({
                value: module.id.toString(),
                label: module.module_label
            }));
            return modules;
        }
        // Handle input_module select
        async function setInputModule() {
            if (inModuleId) {
                const modules = await fetchModules();
                inModuleId.innerHTML = '<option value="">Select Module</option>';
                modules.forEach(module => {
                    inModuleId.innerHTML += `<option value="${module.value}">${module.label}</option>`;
                });
            }
        }
        // Handle edit_module select
        async function setEditModule() {
            if (edModuleId) {
                const modules = await fetchModules();
                edModuleId.innerHTML = '<option value="">Select Module</option>';
                modules.forEach(module => {
                    edModuleId.innerHTML += `<option value="${module.value}">${module.label}</option>`;
                });
            }
        }
        // Handle short_by_module select
        async function setShortByModule() {
            if (syModule) {
                const modules = await fetchModules();
                syModule.innerHTML = '<option value="">All Module</option>';
                modules.forEach(module => {
                    syModule.innerHTML += `<option value="${module.value}">${module.label}</option>`;
                });
            }
        }
        // Run all functions when the page loads
        document.addEventListener('DOMContentLoaded', async function () {
            await setEditModule();
            await setInputModule();
            await setShortByModule();
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- GET DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        async function fetchData(syVSearch, syVLimit, syVModule, syVAction) {
            try {
                let url = `{{ route('app.users.modules.action.data') }}`;
                let params = new URLSearchParams();

                if (syVSearch && syVSearch.trim() !== '') {
                    params.append('search', syVSearch.trim());
                }
                if (syVLimit != undefined && syVLimit != null && syVLimit != '') {
                    params.append('limit', syVLimit);
                }
                if (syVModule && syVModule.trim() !== '') {
                    params.append('module', syVModule.trim());
                }
                if (syVAction && syVAction.trim() !== '') {
                    params.append('action', syVAction.trim());
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
                        <td class="ps-4">ID:${item.id}</td>
                        <td>${item.module_label} | ${item.module_name}</td>
                        <td>${item.action_name?item.action_name:''}</td>
                        <td>${item.created_at?item.created_at:''}</td>
                        <td>${item.updated_at?item.updated_at:''}</td>
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
            const syVModule = syModule.value;
            const syVAction = syAction.value;
            fetchData(syVSearch, syVLimit, syVModule, syVAction);
        });
        // GETDATA BY SEARCH
        sySearch.addEventListener('keyup', (event) => {
            const syVSearch = event.target.value.trim();
            const syVLimit = syLimit.value;
            const syVModule = syModule.value;
            const syVAction = syAction.value;
            fetchData(syVSearch, syVLimit, syVModule, syVAction);
        });
        // GETDATA BY LIMIT
        syLimit.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = event.target.value.trim();
            const syVModule = syModule.value;
            const syVAction = syAction.value;
            fetchData(syVSearch, syVLimit, syVModule, syVAction);
        });
        // GETDATA BY MODULE
        syModule.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = syLimit.value;
            const syVModule = event.target.value.trim();
            const syVAction = syAction.value;
            fetchData(syVSearch, syVLimit, syVModule, syVAction);
        });
        // GETDATA BY ACTION
        syAction.addEventListener('change', (event) => {
            const syVSearch = sySearch.value;
            const syVLimit = syLimit.value;
            const syVModule = syModule.value;
            const syVAction = event.target.value.trim();
            fetchData(syVSearch, syVLimit, syVModule, syVAction);
        });
    </script>
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    {{-- UPDATE DATA --}}
    {{-- -------------------------------------------------------------------------------------------------------------------------------------------------------- --}}
    <script>
        document.addEventListener('click', async function (event) {
            const buttonShowModalEdit = getShowModalEditButton(event.target);
            if (buttonShowModalEdit) {
                event.preventDefault();
                const id = buttonShowModalEdit.dataset.id;
                let url = `{{ route('app.users.modules.action.data') }}`;
                let params = new URLSearchParams();
                params.append('id', id);
                // Hanya tambahkan parameter ke URL jika ada parameter
                if (params.toString()) {
                    url += '?' + params.toString();
                }
                try {
                    const response = await axios.get(url);
                    const data = response.data.data;
                    // Set values to form fields
                    edId.value = data.id;
                    edModuleId.value = data.module_id;
                    edActionName.value = data.action_name;
                    new bootstrap.Modal(componentModalFormEdit).show();

                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }
        });
        buttonUpdate.addEventListener('click', async function () {
            buttonUpdate.style.display = 'none';
            buttonUpdateSend.style.display = 'inline-block';
            try {
                const responseData = await axios.post(`{{ route('app.users.modules.action.update') }}`, {
                    id: edId.value,
                    module_id: edModuleId.value,
                    action_name: edActionName.value,
                });
                let response = responseData.data;
                if (response.status === true) {
                    alertResponseSuccess(response.message);
                    // refresh fetchData
                    const syVSearch = sySearch.value;
                    const syVLimit = syLimit.value;
                    const syVModule = syModule.value;
                    const syVAction = syAction.value;
                    fetchData(syVSearch, syVLimit, syVModule, syVAction);
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
</body>

</html>
