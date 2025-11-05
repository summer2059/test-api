<?php

namespace App\Http\Requests\Api\V1;

use App\Permission\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTicketRequest extends BestTicketRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $authorIdAttribute = $this->routeIs('tickets.store') ? 'data.relationships.author.data.id' : 'author';
        $user = Auth::user();

        $authorRule = 'required|integer|exists:users,id';
        $rules = [
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.status' => 'required|string|in:A,C,H,R',
        ];

        // Only add this if we have a logged-in user (during real runtime)
        if ($user) {
            $rules[$authorIdAttribute] = $authorRule . '|size:' . $user->id;
        }

        // If user can create their own ticket, relax author rule
        if ($user && $user->tokenCan(Abilities::CreateOwnTicket)) {
            $rules[$authorIdAttribute] = $authorRule;
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->routeIs('authors.tickets.store')) {
            $this->merge([
                'author' => $this->route('author'),
            ]);
        }
    }
}
