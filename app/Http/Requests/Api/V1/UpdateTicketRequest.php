<?php

namespace App\Http\Requests\Api\V1;

use App\Permission\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTicketRequest extends BestTicketRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'data.attributes.title' => 'sometimes|string',
            'data.attributes.description' => 'sometimes|string',
            'data.attributes.status' => 'sometimes|string|in:A,C,H,R',
            'data.relationships.author.data.id' => 'prohibited',
        ];

        $user = Auth::user();

        if ($user && $user->tokenCan(Abilities::UpdateOwnTicket)) {
            $rules['data.relationships.author.data.id'] = 'prohibited';
        }

        return $rules;
    }
}
