<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use UserDTO;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $users = User::paginate($perPage);
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
              'name' => 'required|string|max:255',
              'email' => 'required|string|email|max:255|unique:users',
              'password' => 'required|string|min:8|confirmed',
         ]);

        $user = User::create([
              'name' => $request->name,
              'email' => $request->email,
              'password' => bcrypt($request->password),
         ]);

         $data = new UserDTO($user->id, $user->name, $user->email);
        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $user = User::findOrFail($id);
        $data = new UserDTO($user->id, $user->name, $user->email);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $user = User::findOrFail($id);
        $request->validate([
              'name' => 'sometimes|required|string|max:255',
              'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
              'password' => 'sometimes|required|string|min:8|confirmed',
         ]);

        if ($request->has('name')) {
              $user->name = $request->name;
         }
        if ($request->has('email')) {
              $user->email = $request->email;
         }
        if ($request->has('password')) {
              $user->password = bcrypt($request->password);
         }
        $user->save();

        $data = new UserDTO($user->id, $user->name, $user->email);
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
