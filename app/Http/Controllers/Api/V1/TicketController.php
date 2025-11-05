<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Models\Ticket;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\User;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class TicketController extends ApiController
{
    protected $policyClass = TicketPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        // if ($this->include('author')){

        //     return TicketResource::collection(Ticket::with('user')->paginate());
        // }

        // return TicketResource::collection(Ticket::paginate());
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        // $user = User::findOrFail($request->input('data.relationships.author.data.id'));
        if ($this->isAble('store', Ticket::class)) {

            return new TicketResource(Ticket::create($request->mappedAttributes()));
        }        
        return $this->error('You are not authorized', 401);
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        try{
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->include('author')){
    
                return new TicketResource($ticket->load('user'));
            }
            return new TicketResource($ticket);
        } catch (ModelNotFoundException $expeption) {
            return $this->ok('Ticket not found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            // $request->user()->tokenCan('ticket:update');
            // $request->user()->id == $ticket->user_id;
            //policy
            if ($this->isAble('update', $ticket)) {
                $ticket->update( $request->mappedAttributes());
                return new TicketResource($ticket);
            }
            return $this->error('You are not authorized to update this ticket', 404);

        } catch (ModelNotFoundException $expeption) {
            return $this->error('Ticket not found', 404);
        } 
    }

    public function replace(ReplaceTicketRequest $request, $ticket_id) {
        // PUT
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->isAble('replace', $ticket)) {
                $ticket->update( $request->mappedAttributes());
                return new TicketResource($ticket);
            }
            return $this->error('You are not authorized to update this ticket', 404);
        } catch (ModelNotFoundException $expeption) {
            return $this->error('Ticket not found', 404);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->isAble('delete', $ticket)) {
                $ticket->delete();
                return $this->ok('Ticket deleted');
            }
            return $this->error('You are not authorized to delete this ticket', 404);
        } catch (ModelNotFoundException $expeption) {
            return $this->error('Ticket not found', 404);
        }
    }
}
