<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $shop = Auth::user();

        if (!$shop) {
            return response()->json(['error' => 'Shop not authenticated'], 401);
        }
    }

    public function home()
    {
        return view('orders.home');
    }

    public function create(Request $request)
    {
        try {
            $shop = Auth::user();

            if (!$shop) {
                return response()->json(['error' => 'Shop not authenticated'], 401);
            }

            $orderInput = $request->input('orderInput'); // Input passed from the frontend

            $mutation = <<<QUERY
                        mutation draftOrderCreate(\$orderInput: DraftOrderInput!) {
                            draftOrderCreate(input: \$orderInput) {
                                draftOrder {
                                    id
                                    appliedDiscount {
                                        value
                                    }
                                    lineItems(first:1) {
                                        edges {
                                            node {
                                                appliedDiscount{
                                                    value
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        QUERY;

            $response = $shop->api()->graph($mutation, ['orderInput' => $orderInput]);

            // dd($response);
            if (isset($response['errors']) && $response['errors']) {
                return response()->json(['error' => 'API request failed', 'details' => $response], 500);
            }

            $data = $response['body']->container['data']['draftOrderCreate'];

            if (!empty($data['userErrors'])) {
                return response()->json(['error' => $data['userErrors']], 400);
            }

            return response()->json([
                'draftOrder' => $data['draftOrder'],
                'message' => 'Draft order created successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $shop = Auth::user();

            if (!$shop) {
                return response()->json(['error' => 'Shop not authenticated'], 401);
            }

            $updateOrderInput = $request->input('updateOrderInput'); // Input passed from the frontend

            $mutation = <<<MUTATION
                        mutation OrderClose(\$updateOrderInput: OrderCloseInput!) {
                            orderClose(input: \$updateOrderInput) {
                                order {
                                    id
                                    canMarkAsPaid
                                    cancelReason
                                    cancelledAt
                                    clientIp
                                    confirmed
                                    closed
                                    currencyCode
                                    email
                                    customer {
                                        displayName
                                        email
                                    }
                                }
                                userErrors {
                                    field
                                    message
                                }
                            }
                        }
                        MUTATION;

            $response = $shop->api()->graph($mutation, ['updateOrderInput' => $updateOrderInput]);

            if (isset($response['errors']) && $response['errors']) {
                return response()->json(['error' => 'API request failed', 'details' => $response], 500);
            }

            $data = $response['body']->container['data']['orderClose'];

            if (!empty($data['userErrors'])) {
                return response()->json(['error' => $data['userErrors']], 400);
            }

            return response()->json([
                'orderClose' => $data,
                'message' => 'Order close successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
