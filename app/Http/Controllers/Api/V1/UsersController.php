<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Models\User;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Policies\V1\UserPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsersController extends APiController
{
    protected $policyClass = UserPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(AuthorFilter $filters)
    {
        // if ($this->include('tickets')){

        //     return UserResource::collection(User::with('tickets')->paginate());
        // }
        // return UserResource::collection(User::paginate());
        return UserResource::collection(
            User::filter($filters)->paginate()
        );
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
    public function store(StoreUserRequest $request)
    {
        try {
            // $user = User::findOrFail($request->input('data.relationships.author.data.id'));
            $this->isAble('store', User::class);

            return new UserResource(User::create($request->mappedAttributes()));
            // TODO create ticket
        } catch (AuthorizationException $expeption) {
            return $this->error('You are not authorized', 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if ($this->include('tickets')){

            return new UserResource($user->load('tickets'));
        }
        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            //policy
            $this->isAble('update', $user);

            $user->update( $request->mappedAttributes());
            return new UserResource($user);
        } catch (ModelNotFoundException $expeption) {
            return $this->error('Ticket not found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to update this ticket', 404);
        }
    }

    public function replace(ReplaceUserRequest $request, $user_id) {
        // PUT
        try {
            $user = User::findOrFail($user_id);
            $this->isAble('replace', $user);
            $user->update( $request->mappedAttributes());
            return new UserResource($user);
        } catch (ModelNotFoundException $expeption) {
            return $this->error('Ticket not found', 404);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            $this->isAble('delete', $user);
            $user->delete();
            return $this->ok('User deleted');
        } catch (ModelNotFoundException $expeption) {
            return $this->error('User not found', 404);
        }
    }
}
