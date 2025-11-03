<?php

use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\AuthController;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//http://localhost:8000/api ,,  http://localhost:8000/api/tickets,, http://localhost:8000/api/tickets/{id}/,, http://localhost:8000/api/tickets/{id}/edit

//universal  resource locator
//tickets
//users
//contracts
Route::apiResource('tickets', TicketController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
