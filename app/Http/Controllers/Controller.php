<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            "status" => false,
            "message" => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
