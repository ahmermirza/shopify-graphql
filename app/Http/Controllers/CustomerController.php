<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function home()
    {
        return view('customers.home');
    }

    public function customers(Request $request)
    { {
            try {
                $shop = Auth::user();

                if (!$shop) {
                    return response()->json(['error' => 'Shop not authenticated'], 401);
                }

                // Prepare the cursor for pagination
                $cursor = $request->input('cursor', '');

                $query = <<<QUERY
                query CustomerList {
                    customers(first: 250) {
                        nodes {
                            firstName
                            lastName
                            email
                            phone
                        }
                    }
                }
            QUERY;

                $response = $shop->api()->graph($query);

                if (isset($response['errors']) && $response['errors']) {
                    return response()->json(['error' => 'API request failed', 'details' => $response], 500);
                }

                $edges = $response['body']->container['data']['customers']['edges'];
                $customers = array_map(fn($edge) => $edge['node'], $edges);

                $pageInfo = $response['body']->container['data']['customers']['pageInfo'];

                // Get the last edge
                $lastEdge = end($edges);
                $nextCursor = $pageInfo['hasNextPage'] ? ($lastEdge['cursor'] ?? null) : null;

                return response()->json([
                    'customers' => $customers,
                    'next_page_cursor' => $nextCursor,
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
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

            $customerInput = $request->input('customerInput'); // Input passed from the frontend

            $mutation = <<<MUTATION
                mutation customerCreate(\$customerInput: CustomerInput!) {
                    customerCreate(input: \$customerInput) {
                        customer {
                            firstName
                            lastName
                            email
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }
                MUTATION;

            $response = $shop->api()->graph($mutation, ['customerInput' => $customerInput]);

            if (isset($response['errors']) && $response['errors']) {
                return response()->json(['error' => 'API request failed', 'details' => $response], 500);
            }

            $data = $response['body']->container['data']['customerCreate'];

            if (!empty($data['userErrors'])) {
                return response()->json(['error' => $data['userErrors']], 400);
            }

            return response()->json([
                'customer' => $data['customer'],
                'message' => 'Customer created successfully!',
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

            $updateCustomerInput = $request->input('updateCustomerInput'); // Input passed from the frontend

            $mutation = <<<MUTATION
                mutation customerCreate(\$updateCustomerInput: CustomerInput!) {
                    customerCreate(input: \$updateCustomerInput) {
                        customer {
                            firstName
                            lastName
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }
                MUTATION;

            $response = $shop->api()->graph($mutation, ['updateCustomerInput' => $updateCustomerInput]);

            if (isset($response['errors']) && $response['errors']) {
                return response()->json(['error' => 'API request failed', 'details' => $response], 500);
            }

            $data = $response['body']->container['data']['customerCreate'];

            if (!empty($data['userErrors'])) {
                return response()->json(['error' => $data['userErrors']], 400);
            }

            return response()->json([
                'customer' => $data['customer'],
                'message' => 'Customer created successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
