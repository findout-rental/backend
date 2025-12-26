<?php

namespace App\Http\Controllers;

use App\Services\WebSocketMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebSocketMessageController extends Controller
{
    protected WebSocketMessageService $wsMessageService;

    public function __construct(WebSocketMessageService $wsMessageService)
    {
        $this->wsMessageService = $wsMessageService;
    }

    /**
     * Handle incoming WebSocket message.
     * This endpoint is called by Reverb when a client sends a message via WebSocket.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'type' => 'error',
            ], 401);
        }

        $data = $request->all();
        $result = $this->wsMessageService->handleMessage($data, $user);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }
}

