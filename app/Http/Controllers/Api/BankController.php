<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 * name="Banks",
 * description="API Endpoints for managing Bank data"
 * )
 */
class BankController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/banks",
     * tags={"Banks"},
     * summary="Retrieve a list of all active banks (id and name only)",
     * @OA\Response(
     * response=200,
     * description="Bank list retrieved successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Bank list retrieved successfully."),
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Commercial Bank")
     * )
     * )
     * )
     * )
     * )
     */
    public function index()
    {
        $bank = Bank::select('id','name')->get();
        return response()->json(
            ApiResponse::success('Bank list retrieved successfully.', $bank)->toArray(),200
        );
    }


    /**
     * @OA\Post(
     * path="/api/banks",
     * tags={"Banks"},
     * summary="Create a new bank",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", example="New City Bank", maxLength=150)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Bank created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Bank")
     * )
     * ),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        try{
            $validateData = $request->validate([
               'name'=>'required|string|max:150|unique:bank,name'
            ]);

            $bank = Bank::create($validateData);
            return response()->json(ApiResponse::success('Bank created successfully.', $bank)->toArray(),201);

        }catch (ValidationException $exception){
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(),422);
        }catch (\Exception $e){
            return response()->json(ApiResponse::error('Error creating bank', [$e->getMessage()])->toArray(),500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/banks/{id}",
     * tags={"Banks"},
     * summary="Retrieve a specific bank by ID",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the bank to retrieve", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Bank retrieved successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Bank")
     * )
     * ),
     * @OA\Response(response=404, description="Bank not found")
     * )
     */
    public function show(string $id)
    {
        $bank = Bank::find($id);
        if(!$bank){
            return response()->json(ApiResponse::error('Bank not found', ['Bank not found with ID: ' . $id]),404);
        }
        return response()->json(ApiResponse::success('Bank retrieved successfully.', $bank)->toArray(),200);
    }

    /**
     * @OA\Put(
     * path="/api/banks/{id}",
     * tags={"Banks"},
     * summary="Update an existing bank",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the bank to update", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Bank Name", maxLength=150, nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Bank updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Bank")
     * )
     * ),
     * @OA\Response(response=404, description="Bank not found"),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id)
    {
        $bank = Bank::find($id);
        if(!$bank){
            return response()->json(ApiResponse::error('Bank not found', ['Bank not found with ID: ' . $id]),404);
        }
        try{
            $validateData = $request->validate([
                'name'=>'sometimes|required|string|max:150|unique:bank,name,'.$id
            ]);
            $bank->update($validateData);
            return response()->json(ApiResponse::success('Bank updated successfully.', $bank)->toArray(),200);
        }catch (ValidationException $exception){
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(),422);
        }catch (\Exception $e){
            return response()->json(ApiResponse::error('Error updating bank', [$e->getMessage()])->toArray(),500);
        }
    }


    /**
     * @OA\Delete(
     * path="/api/banks/{id}",
     * tags={"Banks"},
     * summary="Delete a bank (Soft Delete)",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the bank to delete", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Bank deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Bank deleted successfully.")
     * )
     * ),
     * @OA\Response(response=404, description="Bank not found"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(string $id)
    {
        try{
            $bank = Bank::find($id);
            if(!$bank){
                return response()->json(ApiResponse::error('Bank not found', ['Bank not found with ID: ' . $id]),404);
            }
            $bank->delete();
            return response()->json(ApiResponse::success('Bank deleted successfully.')->toArray(),200);
        } catch (\Exception $e) {
            return response()->json(
                ApiResponse::error('Error deleting bank', [$e->getMessage()])->toArray(), 500
            );
        }
    }
}
