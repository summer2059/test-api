<?php

namespace App\Http\Requests\Api\V1;

use App\Permission\V1\Abilities;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends BestTicketRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $authorIdAttribute = $this->routeIs('tickets.store') ? 'data.relationships.author.data.id' : 'author';
        $rules = [
            'data.attributes.title' => 'required|string',
            'data.attributes.description' => 'required|string',
            'data.attributes.status' => 'required|string|in:A,C,H,R',
            $authorIdAttribute => 'required|integer|exists:users,id',
        ];
        
        $user = $this->user();

        
            if ($this->user()->tokenCan(Abilities::CreateOwnTicket)) {
                $rules[$authorIdAttribute] = '|size:' . $user->id;
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
