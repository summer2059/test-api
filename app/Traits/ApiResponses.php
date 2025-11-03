<?php

namespace App\Traits;
trait ApiResponses{

    protected function ok($message, $data) {
        return $this->success($message, $data, 200);
    }
    public function success($message, $data, $StatusCode = 200) {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $StatusCode
        ], $StatusCode);
    } 

    public function error($message, $StatusCode) {
        return response()->json([
            'message' => $message,
            'status' => $StatusCode
        ], $StatusCode);
    } 
}