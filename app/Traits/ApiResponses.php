<?php

namespace App\Traits;
trait ApiResponses{

    protected function ok($message, $data = []) {
        return $this->success($message, $data, 200);
    }
    protected function success($message, $data = [], $StatusCode = 200) {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $StatusCode
        ], $StatusCode);
    } 

    protected function error($errors = [], $StatusCode = null) {
        if (is_string($errors)) {
            return response()->json([
                'message' => $errors,
                'status' => $StatusCode
            ], $StatusCode);
        }
        return response()->json([
            'errors' => $errors,
        ]);
    } 
    protected function notAuthorized($message) {
        return $this->error([
            'status' => 401,
            'message' => $message,
            'source' => '',
        ]
        );
    }
}