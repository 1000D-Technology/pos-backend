<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\DTO\UserDTO;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get a paginated list of users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/UserDTO")
     *             ),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
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
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
     *             @OA\Property(property="nic", type="string", example="123456789V"),
     *             @OA\Property(property="basic_salary", type="number", format="float", example=50000.00),
     *             @OA\Property(property="contact_no", type="string", example="+1234567890"),
     *             @OA\Property(property="address", type="string", example="123 Main St, City")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserDTO")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
       $request->validate([
              'name' => 'required|string|max:255',
              'email' => 'required|string|email|max:255|unique:users',
              'password' => 'required|string|min:8|confirmed',
              'nic' => 'nullable|string|max:255',
              'basic_salary' => 'nullable|numeric|min:0',
              'contact_no' => 'nullable|string|max:20',
              'address' => 'nullable|string',
         ]);

        $user = User::create([
              'name' => $request->name,
              'email' => $request->email,
              'password' => bcrypt($request->password),
              'nic' => $request->nic,
              'basic_salary' => $request->basic_salary,
              'contact_no' => $request->contact_no,
              'address' => $request->address,
         ]);

         $data = new UserDTO($user->id, $user->name, $user->email, $user->nic, $user->basic_salary, $user->contact_no, $user->address);
         return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user by ID",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *         @OA\JsonContent(ref="#/components/schemas/UserDTO")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $data = new UserDTO($user->id, $user->name, $user->email, $user->nic, $user->basic_salary, $user->contact_no, $user->address);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newsecret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newsecret123"),
     *             @OA\Property(property="nic", type="string", example="987654321V"),
     *             @OA\Property(property="basic_salary", type="number", format="float", example=60000.00),
     *             @OA\Property(property="contact_no", type="string", example="+0987654321"),
     *             @OA\Property(property="address", type="string", example="456 New Street, Town")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserDTO")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        
        $user = User::findOrFail($id);
        $request->validate([
              'name' => 'sometimes|required|string|max:255',
              'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
              'password' => 'sometimes|required|string|min:8|confirmed',
              'nic' => 'nullable|string|max:255',
              'basic_salary' => 'nullable|numeric|min:0',
              'contact_no' => 'nullable|string|max:20',
              'address' => 'nullable|string',
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
        if ($request->has('nic')) {
              $user->nic = $request->nic;
         }
        if ($request->has('basic_salary')) {
              $user->basic_salary = $request->basic_salary;
         }
        if ($request->has('contact_no')) {
              $user->contact_no = $request->contact_no;
         }
        if ($request->has('address')) {
              $user->address = $request->address;
         }
        $user->save();

        $data = new UserDTO($user->id, $user->name, $user->email, $user->nic, $user->basic_salary, $user->contact_no, $user->address);
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
