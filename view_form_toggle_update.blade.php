<script>
    data.forEach(item => {
        dataTable.innerHTML += `
            <tr>
                <td>${item.id}</td>
                <td>
                    <div class="form-check form-switch form-check-custom form-check-solid me-10">
                        <input class="form-check-input h-20px w-35px" type="checkbox" value="${item.id}" ${item.post_status === 'publish' ? 'checked' : ''}>
                        <label class="form-check-label" id="status_value${item.id}">
                            ${item.post_status}
                        </label>
                    </div>
                </td>
                <td>${item.name}</td>
            </tr>
        `;
    });
</script>
<script>
    document.addEventListener('click', async function(e) {
        if (e.target.matches("input[type='checkbox']")) {
            const checkbox = e.target;
            const id = checkbox.value;
            const status = checkbox.checked ? "publish" : "draft";
            
            const statusLabel = document.getElementById('status_value' + id);
            if (statusLabel) {
                statusLabel.textContent = status;
            }
            try {
                const response = await axios.post(`{{ route('app.posts.update.status') }}`, {
                    id: id,
                    status: status,
                    _token: '{{ csrf_token() }}'
                });
                if (response.data.status === true) {
                    alertResponseSuccess(response.data.message);
                    fetchData(sySearch.value, syLimit.value, syStatus.value);
                } else {
                    alertResponseError(response);
                }
            } catch (error) {
                alertResponseError(error);
            }
        }
    });
</script>
