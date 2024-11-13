<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM INPUT</title>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
</head>

<body>
    <form id="invoice_form">
        <!-- Customer Information Section -->
        <div>
            <h2>Customer Information</h2>
            <div>
                <label for="inv_cus_name">Customer Name:</label>
                <input type="text" id="inv_cus_name">
            </div>

            <div>
                <label for="inv_cus_email">Customer Email:</label>
                <input type="email" id="inv_cus_email">
            </div>

            <div>
                <label for="inv_cus_phone">Customer Phone:</label>
                <input type="tel" id="inv_cus_phone">
            </div>
            <div>
                <label for="inv_cus_phone">Files:</label>
                <input type="file" id="inv_files">
            </div>
        </div>
        <!-- Invoice Dates Section -->
        <div>
            <h2>Invoice Dates</h2>
            <div>
                <label for="inv_date">Invoice Date:</label>
                <input type="date" id="inv_date">
            </div>

            <div>
                <label for="inv_files">Due Date:</label>
                <input type="date" id="inv_date_jatuh_tempo">
            </div>
        </div>
        <!-- Invoice Items Section -->
        <div>
            <h2>Invoice Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="item_invoice">
                    <!-- First Row -->
                    <tr>
                        <td><input type="text" class="inv_item_name"></td>
                        <td><input type="text" class="inv_desc"></td>
                        <td><input type="number" class="inv_qty"></td>
                        <td><input type="number" class="inv_price"></td>
                        <td><input type="number" class="inv_total"></td>
                    </tr>
                    <!-- Second Row -->
                    <tr>
                        <td><input type="text" class="inv_item_name"></td>
                        <td><input type="text" class="inv_desc"></td>
                        <td><input type="number" class="inv_qty"></td>
                        <td><input type="number" class="inv_price"></td>
                        <td><input type="number" class="inv_total"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Submit Buttons -->
        <div>
            <button type="button" id="submit_invoice">Create Invoice</button>
            <button type="button" id="submit_invoice_send" style="display: none;">Processing...</button>
        </div>
    </form>
    <!-- =========================================================================================================================================================  -->
    <!-- ALERT  -->
    <!-- =========================================================================================================================================================  -->
    <script src="{{ asset('assets/panel/plugins/custom/sweetalert/sweetalert.set.js') }}"></script>
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
    <!-- =========================================================================================================================================================  -->
    <!-- CREAT INVOICE  -->
    <!-- =========================================================================================================================================================  -->
    <script>
        // Deklarasi elements
        const submitInvoice = document.getElementById('submit_invoice');
        const submitInvoiceSend = document.getElementById('submit_invoice_send');
        const inCusName = document.getElementById('inv_cus_name');
        const inCusEmail = document.getElementById('inv_cus_email');
        const inCusPhone = document.getElementById('inv_cus_phone');
        const inDate = document.getElementById('inv_date');
        const inDateJatuhTempo = document.getElementById('inv_date_jatuh_tempo');
        const inFiles = document.getElementById('inv_files');
        // Function to collect row data
        const collectRowData = () => {
            const rows = document.querySelectorAll('#item_invoice tr');
            const rowData = [];
            rows.forEach(row => {
                const quantity = row.querySelector('.inv_qty').value.replace(/,/g, '');
                const price = row.querySelector('.inv_price').value.replace(/,/g, '');
                const total = row.querySelector('.inv_total').value.replace(/,/g, '');
                rowData.push({
                    item_name: row.querySelector('.inv_item_name').value,
                    description: row.querySelector('.inv_desc').value,
                    quantity: parseFloat(quantity),
                    price: parseFloat(price),
                    total: parseFloat(total)
                });
            });
            return rowData;
        };
        // Event listener untuk submit invoice
        submitInvoice.addEventListener('click', async function () {
            submitInvoice.style.display = 'none';
            submitInvoiceSend.style.display = 'inline-block';
            try {
                // Collect form data
                const formData = new FormData();
                const rowData = collectRowData();
                // Append form fields
                formData.append('inv_cus_name', inCusName.value);
                formData.append('inv_cus_email', inCusEmail.value);
                formData.append('inv_cus_phone', inCusPhone.value);
                formData.append('inv_date', inDate.value);
                formData.append('inv_date_jatuh_tempo', inDateJatuhTempo.value);
                formData.append('invoice_items', JSON.stringify(rowData));
                formData.append('img', inFiles.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                // Make API request using axios
                const responseData = await axios.post(`{{ route('app.invoice.create') }}`, formData);
                const response = responseData.data;
                if (response.status === true) {
                    alertResponseSuccess(response.message);
                    window.location.href = "{{ route('app.invoice.view') }}";
                } else {
                    alertResponseError(response);
                }
            } catch (error) {
                console.error('Error saving invoice:', error);
            } finally {
                submitInvoice.style.display = 'inline-block';
                submitInvoiceSend.style.display = 'none';
            }
        });
    </script>
</body>

</html>
