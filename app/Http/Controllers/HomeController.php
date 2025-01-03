<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
	public function shop()
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}
			// $response = $shop->api()->rest('GET', "/admin/shop.json");
			$response = $shop->api()->graph('
			query shop {
				shop
					{
						name
						currencyCode
						email
						plan {
							displayName
						}
						currencyFormats {
							moneyFormat
						}
						resourceLimits {
							maxProductVariants
							maxProductOptions
						}
					}
			}');

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function theme()
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}
			// $response = $shop->api()->rest('GET', "/admin/shop.json");
			$response = $shop->api()->graph('
			query theme {
				themes(first: 1, roles: MAIN) {
					edges {
						node {
							id
							name
						}
					}
				}
			}');

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function home()
	{
		return view('home');
	}

	public function products(Request $request)
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			// Prepare the cursor for pagination
			$cursor = $request->input('cursor', '');

			$query = <<<QUERY
			query {
				products(first: 250{$this->getAfterCursor($cursor)}) {
					edges {
						node {
							id
							title
							handle
						}
						cursor
					}
					pageInfo {
						hasNextPage
					}
				}
			}
		QUERY;

			$response = $shop->api()->graph($query);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response], 500);
			}

			$edges = $response['body']->container['data']['products']['edges'];
			$products = array_map(fn($edge) => $edge['node'], $edges);

			$pageInfo = $response['body']->container['data']['products']['pageInfo'];

			// Get the last edge
			$lastEdge = end($edges);
			$nextCursor = $pageInfo['hasNextPage'] ? ($lastEdge['cursor'] ?? null) : null;

			return response()->json([
				'products' => $products,
				'next_page_cursor' => $nextCursor,
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function query(Request $request)
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			// Prepare the cursor for pagination
			$cursor = $request->input('cursor', '');

			$query = <<<QUERY
			query {
				products(first: 250{$this->getAfterCursor($cursor)}) {
					edges {
						node {
							id
							title
							handle
						}
						cursor
					}
					pageInfo {
						hasNextPage
					}
				}
			}
		QUERY;

			$response = $shop->api()->graph($query);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response], 500);
			}

			$edges = $response['body']->container['data']['products']['edges'];
			$products = array_map(fn($edge) => $edge['node'], $edges);

			$pageInfo = $response['body']->container['data']['products']['pageInfo'];

			// Get the last edge
			$lastEdge = end($edges);
			$nextCursor = $pageInfo['hasNextPage'] ? ($lastEdge['cursor'] ?? null) : null;

			return response()->json([
				'products' => $products,
				'next_page_cursor' => $nextCursor,
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Helper function to format the after cursor for GraphQL query.
	 *
	 * @param string|null $cursor
	 * @return string
	 */
	private function getAfterCursor(?string $cursor): string
	{
		return $cursor ? ", after: \"$cursor\"" : '';
	}

	public function create(Request $request)
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$productInput = $request->input('productInput'); // Input passed from the frontend

			// $productInput = [
			// 	"category" => "gid://shopify/TaxonomyCategory/aa-1-13-8",
			// 	"collectionsToJoin" => [
			// 		"gid://shopify/Collection/123456789"
			// 	],
			// 	"descriptionHtml" => "This is a sample description of a sweater weather.",
			// 	"productType" => "Cool t-shirts",
			// 	"tags" => [
			// 		"Cool",
			// 		"t-shirts"
			// 	],
			// 	"title" => "Sweater Weather",
			// 	"vendor" => "Hazel",
			// 	"productOptions" => [
			// 		[
			// 			"name" => "Color",
			// 			"values" => [
			// 				[
			// 					"name" => "Red"
			// 				],
			// 				[
			// 					"name" => "Green"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"name" => "Size",
			// 			"values" => [
			// 				[
			// 					"name" => "Small"
			// 				],
			// 				[
			// 					"name" => "Medium"
			// 				]
			// 			]
			// 		]
			// 	]
			// ];
			
			$mutation = <<<MUTATION
				mutation CreateProductWithOptions(\$productInput: ProductCreateInput) {
					productCreate(product: \$productInput) {
						product {
							id
							title
							descriptionHtml
							options {
								name
								values
							}
						}
						userErrors {
							field
							message
						}
					}
				}
			MUTATION;

			$response = $shop->api()->graph($mutation, ['productInput' => $productInput]);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response['errors']], 500);
			}

			$data = $response['body']->container['data']['productCreate'];

			if (!empty($data['userErrors'])) {
				return response()->json(['error' => $data['userErrors']], 400);
			}

			return response()->json([
				'product' => $data['product'],
				'message' => 'Product created successfully!',
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function showProductOptions()
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$productInput = [
				"id" => "gid://shopify/Product/9267991347477"
			];

			$query =  <<<QUERY
				query getProductOptions(\$id: ID!) {
					product(id: \$id) {
						id
						options {
							id
							name
						}
					}
				}
			QUERY;

			$response = $shop->api()->graph($query, [
				'id' => $productInput['id']
			]);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response['errors']], 500);
			}

			$data = $response['body']->container['data'];

			if (!empty($data['userErrors'])) {
				return response()->json(['error' => $data['userErrors']], 400);
			}

			return response()->json([
				'product' => $data['product'],
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function updateProductOptionValues()
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$productInput = [
				"productId" => "gid://shopify/Product/9267991347477",
				"option" => [
					"id" => "gid://shopify/ProductOption/11655570522389"
				],
				"optionValuesToAdd" => [
					[
						"name" => "XLLLL"
					]
				]
			];

			$mutation =  <<<MUTATION
				mutation createOptionsValue(\$productId: ID!, \$option: OptionUpdateInput!, \$optionValuesToAdd: [OptionValueCreateInput!]) {
					productOptionUpdate(productId: \$productId, option: \$option, optionValuesToAdd: \$optionValuesToAdd) {
						product {
							options {
								name
								optionValues {
									name
								}
							}
						}
						userErrors {
							field
							message
							code
						}
					}
				}
			MUTATION;

			$response = $shop->api()->graph($mutation, [
				'productId' => $productInput['productId'],
				'option' => $productInput['option'],
				'optionValuesToAdd' => $productInput['optionValuesToAdd']
			]);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response['errors']], 500);
			}

			$data = $response['body']->container['data']['productOptionUpdate'];

			if (!empty($data['userErrors'])) {
				return response()->json(['error' => $data['userErrors']], 400);
			}

			return response()->json([
				'product' => $data['product'],
				'message' => 'New product option values added successfully!',
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function insertVariants(Request $request)
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}
			$productVariantsInput = $request->input('productVariantsInput'); // Input passed from the frontend

			// $productVariantsInput = [
			// 	"productId" => "",
			// 	"variantsInput" => [
			// 		[
			// 			"price" => 1.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Red"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Small"
			// 				]
			// 			],
			// 			"inventoryItem" => [
			// 				"sku" => "RED_SML"
			// 			]
			// 		],
			// 		[
			// 			"price" => 3.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Red"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Medium"
			// 				]
			// 			],
			// 			"inventoryItem" => [
			// 				"sku" => "RED_MED"
			// 			],
			// 			"inventoryQuantities" => [
			// 				[
			// 					"locationId" => "gid://shopify/Location/95788269845",
			// 					"availableQuantity" => 281
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 6.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Red"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Large"
			// 				]
			// 			],
			// 			"inventoryItem" => [
			// 				"sku" => "RED_LRG",
			// 				"measurement" => [
			// 					"weight" => [
			// 						"unit" => "GRAMS",
			// 						"value" => 250
			// 					]
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 9.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Green"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Small"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 12.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Green"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Medium"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 15.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Green"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Large"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 18.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Blue"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Small"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 21.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Blue"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Medium"
			// 				]
			// 			]
			// 		],
			// 		[
			// 			"price" => 24.99,
			// 			"optionValues" => [
			// 				[
			// 					"optionName" => "Color",
			// 					"name" => "Blue"
			// 				],
			// 				[
			// 					"optionName" => "Size",
			// 					"name" => "Large"
			// 				]
			// 			]
			// 		]
			// 	]
			// ];

			$mutation = <<<MUTATION
			mutation CreateProductVariants(\$productId: ID!, \$variantsInput: [ProductVariantsBulkInput!]!) {
				productVariantsBulkCreate(productId: \$productId, strategy: REMOVE_STANDALONE_VARIANT, variants: \$variantsInput) {
					productVariants {
						id
						title
						sku
						image {
							id
						}
						inventoryQuantity
						inventoryItem {
							id
							inventoryLevels(first: 10) {
								edges {
									node {
										location {
											name
										}
										quantities(names: "available") {
											name
											quantity
										}
									}
								}
							}
						}
						selectedOptions {
							name
							value
						}
					}
					userErrors {
						field
						message
					}
				}
			}
			MUTATION;
			$variantsResponse = $shop->api()->graph($mutation, [
				'productId' => $productVariantsInput['productId'],
				'variantsInput' => $productVariantsInput['variantsInput']
			]);

			if (isset($variantsResponse['errors']) && $variantsResponse['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $variantsResponse['errors']], 500);
			}

			$variantsData = $variantsResponse['body']->container['data']['productVariantsBulkCreate'];
			if (!empty($variantsData['userErrors'])) {
				return response()->json(['error' => $variantsData['userErrors']], 400);
			}

			return response()->json([
				'productVariants' => $variantsData['productVariants'],
				'message' => 'Product variants created successfully!',
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function update(Request $request)
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$updateProductInput = $request->input('updateProductInput'); // Input passed from the frontend

			$mutation = <<<MUTATION
					mutation productUpdate(\$updateProductInput: ProductInput!) {
						productUpdate(input: \$updateProductInput) {
							product {
								id
								title
							}
							userErrors {
								field
								message
							}
						}
					}
					MUTATION;

			$response = $shop->api()->graph($mutation, ['updateProductInput' => $updateProductInput]);

			if (isset($response['errors']) && $response['errors']) {
				return response()->json(['error' => 'API request failed', 'details' => $response], 500);
			}

			$data = $response['body']->container['data']['productUpdate'];

			if (!empty($data['userErrors'])) {
				return response()->json(['error' => $data['userErrors']], 400);
			}

			return response()->json([
				'product' => $data['product'],
				'message' => 'Product updated successfully!',
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function showProductVariants()
	{
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$id = "gid://shopify/Product/9267996229909";

			$query = <<<QUERY
				query productVariants(\$id: ID!) {
					product(id: \$id) {
						variants(first: 10) {
							edges {
								node {
									id
									price
									inventoryQuantity
								}
							}
						}
					}
				}
				QUERY;
			$response = $shop->api()->graph($query, ['id' => $id]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['product']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function updateProductVariants()
	{
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$updateProductVariantsInput = [
				"productId" => "gid://shopify/Product/9267996229909",
				"variants" => [
					[
						"id" => "gid://shopify/ProductVariant/48709070455061",
						"price" => 1.95
					],
					[
						"id" => "gid://shopify/ProductVariant/48709070487829",
						"price" => 3.96
					]
				]
			];

			$mutation = <<<MUTATION
					mutation ProductVariantsUpdate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
						productVariantsBulkUpdate(productId: \$productId, variants: \$variants) {
							productVariants {
								id
								title
								price
								compareAtPrice
							}
							userErrors {
								field
								message
							}
						}
					}
				MUTATION;
			$response = $shop->api()->graph($mutation, [
				'productId' => $updateProductVariantsInput['productId'],
				'variants' => $updateProductVariantsInput['variants']
			]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['productVariantsBulkUpdate']['productVariants']); // response returns all the variants that have been updated.
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function deleteProductVariants()
	{
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$updateProductVariantsInput = [
				"productId" => "gid://shopify/Product/9267996229909",
				"variantsIds" => [
						"gid://shopify/ProductVariant/48709070520597",
						"gid://shopify/ProductVariant/48709070586133",
				]
			];

			$mutation = <<<MUTATION
					mutation productVariantsBulkDelete(\$productId: ID!, \$variantsIds: [ID!]!) {
						productVariantsBulkDelete(productId: \$productId, variantsIds: \$variantsIds) {
							product {
								id
								title
							}
							userErrors {
								field
								message
							}
						}
					}
				MUTATION;
			$response = $shop->api()->graph($mutation, [
				'productId' => $updateProductVariantsInput['productId'],
				'variantsIds' => $updateProductVariantsInput['variantsIds']
			]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['productVariantsBulkDelete']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function showLocations() {
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$query = <<<QUERY
				query locations {
					locations(first: 1, query: "shop") {
						edges {
							node {
								name
								id
							}
						}
					}
				}
				QUERY;

			$response = $shop->api()->graph($query);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['locations']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function showInventoryItems() {
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$id = "gid://shopify/Product/9267996229909";

			$query = <<<QUERY
				query inventoryItem(\$id: ID!) {
					product(id: \$id) {
						variants(first: 9) {
							edges {
								node {
									title
									inventoryItem {
										id
									}
								}
							}
						}
					}
				}
				QUERY;

			$response = $shop->api()->graph($query, ['id' => $id]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['product']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function setInventoryItemsQuantity()
	{
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			$input = [
				"name" => "available", 
				"reason" => "correction", 
				"ignoreCompareQuantity" => true, 
				"quantities" => [
					[
						"inventoryItemId" => "gid://shopify/InventoryItem/50691514466581", 
						"locationId" => "gid://shopify/Location/95788269845", 
						"quantity" => 45,
						"compareQuantity" => 45
					]
				] 
			];			

			$mutation = <<<MUTATION
					mutation inventorySetQuantities(\$input: InventorySetQuantitiesInput!) {
						inventorySetQuantities(input: \$input) {
							inventoryAdjustmentGroup {
								createdAt
								reason
								referenceDocumentUri
								changes {
									name
									delta
									quantityAfterChange
									location {
										id
										name
									}
								}
							}
							userErrors {
								field
								message
							}
						}
					}
				MUTATION;
			$response = $shop->api()->graph($mutation, ['input' => $input]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']['data']['inventorySetQuantities']['inventoryAdjustmentGroup']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function showList()
	{
		try {

			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}

			// $productId = "9258972250389";
			// $response = $shop->api()->rest('GET', "/admin/api/2024-10/products/{$productId}.json");

			$ids = [
				"gid://shopify/Product/9258977362197",
				"gid://shopify/Product/9258972250389",
				"gid://shopify/Product/9258972315925"
			];

			$query = <<<QUERY
					query GetProductsByIds(\$ids: [ID!]!) {
						nodes(ids: \$ids) {
							id
							... on Product {
								title
								handle
								onlineStorePreviewUrl
								options {
									id
									name
									optionValues {
										id
										name
									}
								}
								variants(first: 10, sortKey: TITLE) {
									edges {
										node {
											id
											price
											title
											inventoryQuantity
											position
										}
									}
								}
							}
						}
					}
				QUERY;

			// $query = <<<QUERY
			// 	query {
			// 		productByHandle(handle: "15mm-combo-wrench") {
			// 		id
			// 		title
			// 		productType
			// 		vendor
			// 		}
			// 	}
			// QUERY;
			$response = $shop->api()->graph($query, ['ids' => $ids]);

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function count()
	{
		try {
			$shop = Auth::user();

			if (!$shop) {
				return response()->json(['error' => 'Shop not authenticated'], 401);
			}
			$response = $shop->api()->rest('GET', "/admin/api/2024-10/products/count.json");

			if ($response['errors']) {
				return response($response);
			}

			return response($response['body']);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}
}
