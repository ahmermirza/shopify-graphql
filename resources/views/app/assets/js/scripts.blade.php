<script>
    $(document).ready(function() {
        let createdProductId = null;
        const products = [];
        let total = 0;

        // Fetch products recursively using the Shopify cursor-based pagination
        const fetchProducts = (cursor = null) => {
            const apiURL = cursor ?
                `{{ route('products') }}?cursor=${cursor}` :
                '{{ route('products') }}';

            $.ajax({
                url: apiURL,
                method: 'GET',
                beforeSend: () => {
                    if (!products.length) {
                        $('#products').html('<p>Loading products. Please wait...</p>');
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

            // Append current batch of products to products[]
            const currentProducts = data.products || [];
            total += currentProducts.length;
            products.push(...currentProducts);

            // Continue fetching if more pages exist; otherwise, render products
            if (data.next_page_cursor) {
                fetchProducts(data.next_page_cursor);
            } else {
                renderProducts();
            }
        };

        // Handle API error
        const handleError = (xhr) => {
            console.error('Error:', xhr.responseText);
            displayError('Failed to load products. Please try again later.');
        };

        // Render fetched products to the DOM
        const renderProducts = () => {
            if (!products.length) {
                $('#products').html('<p>No products found.</p>');
                return;
            }

            const productList = products.map(
                (product) =>
                `<li><strong>${product.title}</strong> - ID: ${product.id} - <strong>Handle:</strong> ${product.handle}</li>`
            );

            const html = `
            <h3>Total Products: ${total}</h3>
            <ul>${productList.join('')}</ul>
        `;
            $('#products').html(html);
        };

        // Display error message
        const displayError = (message) => {
            $('#products').html(`<p>${message}</p>`);
        };

        const productInput = {
            "category": "gid://shopify/TaxonomyCategory/aa-1-13-8",
            "collectionsToJoin": [
                "gid://shopify/Collection/123456789"
            ],
            "descriptionHtml": "This is a sample description of a sweater weather.",
            "productType": "Cool t-shirts",
            "tags": [
                "Cool",
                "t-shirts"
            ],
            "title": "Sweater Weather",
            "vendor": "Hazel",
            "productOptions": [{
                    "name": "Color",
                    "values": [{
                            "name": "Red"
                        },
                        {
                            "name": "Green"
                        }
                    ]
                },
                {
                    "name": "Size",
                    "values": [{
                            "name": "Small"
                        },
                        {
                            "name": "Medium"
                        }
                    ]
                }
            ]
        };

        const createProduct = (productInput) => {
            $.ajax({
                url: "{{ route('create') }}",
                method: "POST",
                data: {
                    productInput: productInput, // Pass the product input data
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $('#status').html('<p>Creating product. Please wait...</p>');
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

            const product = data.product;
            createdProductId = product.id;
            $('#tempProductId').text(product.id);
            $('#status').html(
                `<p>Product with standalone variant created successfully!<br><strong>ID:</strong> ${product.id}<br><strong>Title:</strong> ${product.title}</p>`
            );

            // Added 2 product options with 2 value, but 3 values of the same 2 options while creating variants & variants with all 3 option values were added to that product.
            createProductVariants(productVariantsInput);
        };

        // Handle API error
        const handleCreateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#status').html('<p>Failed to create product. Please try again later.</p>');
        };

        // Call the function to create a product
        $('#createProductButton').click(function() {
            createProduct(productInput);
        });

        const productVariantsInput = {
            "productId": "",
            "variantsInput": [{
                    "price": 1.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Red"
                        },
                        {
                            "optionName": "Size",
                            "name": "Small"
                        }
                    ],
                    "inventoryItem": {
                        "sku": "RED_SML"
                    }
                },
                {
                    "price": 3.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Red"
                        },
                        {
                            "optionName": "Size",
                            "name": "Medium"
                        }
                    ],
                    "inventoryItem": {
                        "sku": "RED_MED"
                    },
                    "inventoryQuantities": [{
                        "locationId": "gid://shopify/Location/95788269845",
                        "availableQuantity": 281
                    }]
                },
                {
                    "price": 6.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Red"
                        },
                        {
                            "optionName": "Size",
                            "name": "Large"
                        }
                    ],
                    "inventoryItem": {
                        "sku": "RED_LRG",
                        "measurement": {
                            "weight": {
                                "unit": "GRAMS",
                                "value": 250
                            }
                        }
                    }
                },
                {
                    "price": 9.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Green"
                        },
                        {
                            "optionName": "Size",
                            "name": "Small"
                        }
                    ]
                },
                {
                    "price": 12.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Green"
                        },
                        {
                            "optionName": "Size",
                            "name": "Medium"
                        }
                    ]
                },
                {
                    "price": 15.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Green"
                        },
                        {
                            "optionName": "Size",
                            "name": "Large"
                        }
                    ]
                },
                {
                    "price": 18.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Blue"
                        },
                        {
                            "optionName": "Size",
                            "name": "Small"
                        }
                    ]
                },
                {
                    "price": 21.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Blue"
                        },
                        {
                            "optionName": "Size",
                            "name": "Medium"
                        }
                    ]
                },
                {
                    "price": 24.99,
                    "optionValues": [{
                            "optionName": "Color",
                            "name": "Blue"
                        },
                        {
                            "optionName": "Size",
                            "name": "Large"
                        }
                    ]
                }
            ]
        };

        const createProductVariants = (productVariantsInput) => {

            productVariantsInput['productId'] = $('#tempProductId').text();
            $.ajax({
                url: "{{ route('product.variants.insert') }}", // Laravel route for create
                method: "POST",
                data: JSON.stringify({
                    productVariantsInput: productVariantsInput, // Pass the product input data
                    _token: "{{ csrf_token() }}" // Include CSRF token for security
                }),
                contentType: "application/json",
                beforeSend: function() {
                    $('#status').html('<p>Creating product variants. Please wait...</p>');
                },
                success: handleCreateVariantSuccess,
                error: handleCreateVariantError,
            });
        }

        const handleCreateVariantSuccess = (data) => {
            if (data.error) {
                $('#status').html(`<p>Error: ${data.error}</p>`);
                return;
            }

            const productVariants = data.productVariants;

            let content = '<p>Product and variants created successfully!</p>';
            productVariants.forEach((variant, index) => {
                content += `Variant ${index + 1}: `;
                content += `<strong>Title:</strong> ${variant.title}<br>`;
                content += `<strong>Options:</strong><ul style='margin-top: 5px;'>`;
                variant.selectedOptions.forEach(option => {
                    content += `<li><strong>${option.name}:</strong> ${option.value}</li>`;
                });

                content += `</ul>`;
            });

            // Append the content to the status div
            $('#status').html(content);

            $('#updateProductButton').show();
        };

        // Handle API error
        const handleCreateVariantError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#status').html('<p>Failed to create product variants. Please try again later.</p>');
        };

        // -----------------------------Update Product--------------------------------

        const updateProductInput = {
            title: "Sample Product 45",
        };

        const updateProduct = (updateProductInput) => {

            updateProductInput["id"] = $('#tempProductId').text();

            $.ajax({
                url: "{{ route('product.update') }}", // Laravel route for create
                method: "POST",
                data: {
                    updateProductInput: updateProductInput, // Pass the product input data
                    _token: "{{ csrf_token() }}", // Include CSRF token for security
                    _method: 'patch',
                },
                beforeSend: function() {
                    $('#update-status').html('<p>Updating product. Please wait...</p>');
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

            const product = data.product;
            $('#update-status').html(
                `<p>Product updated successfully!<br><strong>Product Name:</strong> ${product.title}<br><strong>ID:</strong> ${product.id}</p>`
            );
        };

        // Handle API error
        const handleUpdateError = (xhr) => {
            console.error('Error:', xhr.responseText);
            $('#update-status').html('<p>Failed to update product. Please try again later.</p>');
        };

        // Call the function to update a product
        $('#updateProductButton').click(function() {
            updateProduct(updateProductInput);
        });
        // -----------------------------Update Product Ends---------------------------

        // Start fetching products
        setTimeout(() => fetchProducts(), 500);
    });
</script>
