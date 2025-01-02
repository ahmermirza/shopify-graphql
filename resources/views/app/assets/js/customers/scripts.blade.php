<script>
    $(document).ready(function() {
        const customers = [];
        let total = 0;

        // Fetch customers recursively using the Shopify cursor-based pagination
        const fetchCustomers = (cursor = null) => {
            const apiURL = cursor ?
                `{{ route('customers') }}?cursor=${cursor}` :
                '{{ route('customers') }}';

            $.ajax({
                url: apiURL,
                method: 'GET',
                beforeSend: () => {
                    if (!customers.length) {
                        $('#customers').html('<p>Loading customers. Please wait...</p>');
                    }
                },
                success: handleSuccess,
                error: handleError,
            });
        };

        // Handle successful API response
        const handleSuccess = (data) => {
            if (data.error) {
                displayError(`Error: ${data.error}`);
                return;
            }

            // Append current batch of customers to customers[]
            const currentCustomers = data.customers || [];
            total += currentCustomers.length;
            customers.push(...currentCustomers);

            // Continue fetching if more pages exist; otherwise, render customers
            if (data.next_page_cursor) {
                fetchCustomers(data.next_page_cursor);
            } else {
                renderCustomers();
            }
        };

        // Handle API error
        const handleError = (xhr) => {
            console.error('Error:', xhr.responseText);
            displayError('Failed to load customers. Please try again later.');
        };

        // Render fetched customers to the DOM
        const renderCustomers = () => {
            if (!customers.length) {
                $('#customers').html('<p>No customers found.</p>');
                return;
            }

            const customerList = customers.map(
                (customer) =>
                `<li><strong>${customer.firstName}</strong> - ID: ${customer.id}</li>`
            );

            const html = `
            <h3>Total Customers: ${total}</h3>
            <ul>${customerList.join('')}</ul>
        `;
            $('#customers').html(html);
        };

        // Display error message
        const displayError = (message) => {
            $('#customers').html(`<p>${message}</p>`);
        };

        const customerInput = {
            firstName: "John",
            lastName: "Doe",
            email: "john1doe5065090257680@testcase.com",
        };

        const createCustomer = (customerInput) => {

            $.ajax({
                url: "{{ route('customer.create') }}", // Laravel route for create
                method: "POST",
                data: {
                    customerInput: customerInput, // Pass the customer input data
                    _token: "{{ csrf_token() }}" // Include CSRF token for security
                },
                beforeSend: function() {
                    $('#status').html('<p>Creating customer. Please wait...</p>');
                },
                success: handleCreateSuccess,
                error: handleCreateError,
            });
        }

        // Handle successful API response
        const handleCreateSuccess = (data) => {
            if (data.error) {
                $('#status').html(`<p>Error: ${data.error}</p>`);
                return;
            }

            const customer = data.customer;
            $('#status').html(
                `<p>Customer created successfully!<br><strong>Full Name:</strong> ${customer.firstName} ${customer.lastName}<br><strong>Email:</strong> ${customer.email}</p>`
            );
        };

        // Handle API error
        const handleCreateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#status').html('<p>Failed to create customer. Please try again later.</p>');
        };

        // Call the function to create a customer
        $('#createCustomerButton').click(function() {
            createCustomer(customerInput);
        });

        // -----------------------------Update Customer--------------------------------
        const updateCustomerInput = {
            firstName: "Ryan",
            lastName: "Carter",
        };

        const updateCustomer = (updateCustomerInput) => {

            $.ajax({
                url: "{{ route('customer.update') }}", // Laravel route for create
                method: "POST",
                data: {
                    updateCustomerInput: updateCustomerInput, // Pass the customer input data
                    _token: "{{ csrf_token() }}" // Include CSRF token for security
                },
                beforeSend: function() {
                    $('#update-status').html('<p>Updating customer. Please wait...</p>');
                },
                success: handleUpdateSuccess,
                error: handleUpdateError,
            });
        }

        // Handle successful API response
        const handleUpdateSuccess = (data) => {
            if (data.error) {
                $('#update-status').html(`<p>Error: ${data.error}</p>`);
                return;
            }

            const customer = data.customer;
            $('#update-status').html(
                `<p>Customer updated successfully!<br><strong>Full Name:</strong> ${customer.firstName} ${customer.lastName}<br><strong>Email:</strong> ${customer.email}</p>`
            );
        };

        // Handle API error
        const handleUpdateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#update-status').html('<p>Failed to update customer. Please try again later.</p>');
        };

        // Call the function to update a customer
        $('#updateCustomerButton').click(function() {
            updateCustomer(updateCustomerInput);
        });
        // -----------------------------Update Customer Ends---------------------------

        // Start fetching customers
        // setTimeout(() => fetchCustomers(), 500);
    });
</script>
