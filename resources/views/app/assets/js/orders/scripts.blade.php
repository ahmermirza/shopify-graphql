<script>
    $(document).ready(function() {

        // Display error message
        const displayError = (message) => {
            $('#orders').html(`<p>${message}</p>`);
        };

        // -----------------------------Create Order--------------------------------

        const orderInput = {
            customerId: "gid://shopify/Customer/9537973289237",
            note: "Test draft order",
            email: "ahmer.mirza@unitedsol.net",
            tags: ["foo", "bar"],
            shippingLine: {
                title: "Custom Shipping",
                price: 4.55
            },
            shippingAddress: {
                address1: "123 Main St",
                city: "Waterloo",
                province: "Ontario",
                country: "Canada",
                zip: "A1A 1A1"
            },
            billingAddress: {
                address1: "456 Main St",
                city: "Toronto",
                province: "Ontario",
                country: "Canada",
                zip: "Z9Z 9Z9"
            },
            appliedDiscount: {
                description: "damaged",
                value: 5.0,
                valueType: "FIXED_AMOUNT",
                title: "Custom"
            },
            lineItems: [
                {
                    title: "Custom product",
                    originalUnitPrice: 14.99,
                    quantity: 5,
                    appliedDiscount: {
                        description: "wholesale",
                        value: 5.0, // Explicitly set as Float
                        valueType: "PERCENTAGE",
                        title: "Fancy"
                    },
                    weight: {
                        value: 1.0, // Explicitly set as Float
                        unit: "KILOGRAMS"
                    },
                    customAttributes: [
                        {
                            key: "color",
                            value: "Gold"
                        },
                        {
                            key: "material",
                            value: "Plastic"
                        }
                    ]
                },
                {
                    variantId: "gid://shopify/ProductVariant/48676297736469",
                    quantity: 2
                }
            ],
            customAttributes: [
                {
                    key: "name",
                    value: "Achilles"
                },
                {
                    key: "city",
                    value: "Troy"
                }
            ]
        };


        const createOrder = (orderInput) => {
            
            $.ajax({
                url: "{{ route('order.create') }}", // Laravel route for create
                method: "POST",
                data: JSON.stringify({
                    orderInput: orderInput, // Pass the order input data
                    _token: "{{ csrf_token() }}" // Include CSRF token for security
                }),
                contentType: "application/json",
                beforeSend: function() {
                    $('#create-status').html('<p>Creating draft order. Please wait...</p>');
                },
                success: handleCreateSuccess,
                error: handleCreateError,
            });
        }

        // Handle successful API response
        const handleCreateSuccess = (data) => {
            if (data.error) {
                $('#create-status').html(`<p>Error: ${data.error}</p>`);
                return;
            }

            const draftOrder = data.draftOrder;
            $('#create-status').html(
                `<p>Draft order created successfully!<br><strong>ID:</strong> ${draftOrder.id}<br></p>`
            );
            $('#closeOrderButton').show();
        };

        // Handle API error
        const handleCreateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#create-status').html('<p>Failed to create draft order. Please try again later.</p>');
        };

        // Call the function to create a order
        $('#createOrderButton').click(function() {
            createOrder(orderInput);
        });
        // -----------------------------Create Order Ends---------------------------

        // -----------------------------Update Order--------------------------------
        const updateOrderInput = {
            id: "gid://shopify/Order/6121750003989",
        };

        const updateOrder = (updateOrderInput) => {

            $.ajax({
                url: "{{ route('order.update') }}",
                method: "POST",
                data: {
                    updateOrderInput: updateOrderInput, // Pass the order input data
                    _token: "{{ csrf_token() }}", // Include CSRF token for security
                },
                beforeSend: function() {
                    $('#status').html('<p>Closing order. Please wait...</p>');
                },
                success: handleUpdateSuccess,
                error: handleUpdateError,
            });
        }

        // Handle successful API response
        const handleUpdateSuccess = (data) => {
            if (data.error) {
                $('#status').html(`<p>Error: ${data.error}</p>`);
                return;
            }

            const order = data.orderClose.order;
            $('#status').html(
                `<p>Order closed successfully!<br><br><strong>Email:</strong> ${order.email}<br><strong>ID:</strong> ${order.id}<br><strong>Status:</strong> ${order.closed ? 'closed!' : order.closed}</p>`
            );
        };

        // Handle API error
        const handleUpdateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#status').html('<p>Failed to close the order. Please try again later.</p>');
        };

        // Call the function to update a order
        $('#closeOrderButton').click(function() {
            updateOrder(updateOrderInput);
        });
        // -----------------------------Update Order Ends---------------------------
    });
</script>
