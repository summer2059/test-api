<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Policies\V1\TicketPolicy;
use App\Traits\ApiResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

/**
 * @group Tickets
 *
 * APIs for managing support tickets.
 *
 * These endpoints allow clients to list, create, update, and delete tickets.
 * Tickets can be filtered by query parameters and include related resources (like authors).
 */
class TicketController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    /**
     * Display a paginated listing of tickets.
     *
     * @queryParam include string Optional. Include related resources (e.g., `author`). Example: author
     * @queryParam status string Optional. Filter tickets by status. Example: A
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Server issue",
     *       "description": "Database connection timeout",
     *       "status": "A",
     *       "created_at": "2025-11-05T10:00:00.000000Z",
     *       "updated_at": "2025-11-05T10:00:00.000000Z",
     *       "user": {
     *         "id": 3,
     *         "name": "John Doe"
     *       }
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     *
     * @param \App\Http\Filters\V1\TicketFilter $filters
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Store a newly created ticket.
     *
     * @bodyParam data.attributes.title string required The title of the ticket. Example: Server down
     * @bodyParam data.attributes.description string required A detailed description of the issue. Example: Database connection failure on production.
     * @bodyParam data.attributes.status string required The status code of the ticket (A, C, H, R). Example: A
     * @bodyParam data.relationships.author.data.id integer required The ID of the ticket author. Example: 1
     *
     * @response 201 {
     *   "data": {
     *     "id": 10,
     *     "title": "Server down",
     *     "description": "Database connection failure on production.",
     *     "status": "A",
     *     "created_at": "2025-11-05T10:00:00.000000Z",
     *     "updated_at": "2025-11-05T10:00:00.000000Z"
     *   }
     * }
     *
     * @param \App\Http\Requests\Api\V1\StoreTicketRequest $request
     * @return \App\Http\Resources\V1\TicketResource|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreTicketRequest $request)
    {
        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes()));
        }

        return $this->notAuthorized('You are not authorized');
    }

    /**
     * Display the specified ticket.
     *
     * @urlParam ticket_id integer required The ID of the ticket. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Server issue",
     *     "description": "Database connection timeout",
     *     "status": "A",
     *     "created_at": "2025-11-05T10:00:00.000000Z",
     *     "updated_at": "2025-11-05T10:00:00.000000Z"
     *   }
     * }
     *
     * @param int $ticket_id
     * @return \App\Http\Resources\V1\TicketResource
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($ticket_id)
    {
        $ticket = Ticket::findOrFail($ticket_id);

        if ($this->include('author')) {
            return new TicketResource($ticket->load('user'));
        }

        return new TicketResource($ticket);
    }

    /**
     * Update the specified ticket.
     *
     * @urlParam ticket integer required The ID of the ticket. Example: 1
     * @bodyParam data.attributes.title string optional The new title of the ticket. Example: Updated server issue
     * @bodyParam data.attributes.status string optional The new status (A, C, H, R). Example: C
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "title": "Updated server issue",
     *     "description": "Database fixed",
     *     "status": "C"
     *   }
     * }
     *
     * @param \App\Http\Requests\Api\V1\UpdateTicketRequest $request
     * @param \App\Models\Ticket $ticket
     * @return \App\Http\Resources\V1\TicketResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        if ($this->isAble('update', $ticket)) {
            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You are not authorized to update this ticket');
    }

    /**
     * Replace the specified ticket (PUT).
     *
     * This completely replaces the existing resource with the given attributes.
     *
     * @urlParam ticket integer required The ID of the ticket. Example: 1
     * @bodyParam data.attributes.title string required The title of the ticket. Example: Network error
     * @bodyParam data.attributes.description string required Description of the issue. Example: Firewall blocking port 3306
     * @bodyParam data.attributes.status string required Ticket status (A, C, H, R). Example: H
     *
     * @param \App\Http\Requests\Api\V1\ReplaceTicketRequest $request
     * @param \App\Models\Ticket $ticket
     * @return \App\Http\Resources\V1\TicketResource|\Illuminate\Http\JsonResponse
     */
    public function replace(ReplaceTicketRequest $request, Ticket $ticket)
    {
        if ($this->isAble('replace', $ticket)) {
            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You are not authorized to update this ticket');
    }

    /**
     * Delete the specified ticket.
     *
     * @urlParam ticket integer required The ID of the ticket. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Ticket deleted"
     * }
     *
     * @param \App\Models\Ticket $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ticket $ticket)
    {
        if ($this->isAble('delete', $ticket)) {
            $ticket->delete();
            return $this->ok('Ticket deleted');
        }

        return $this->notAuthorized('You are not authorized to delete this ticket');
    }
}
