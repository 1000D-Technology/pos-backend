<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery\Exception;
/**
 * @OA\Tag(
 * name="Companies",
 * description="API Endpoints for managing Company resources"
 * )
 */
class CompanyController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/company",
     * tags={"Companies"},
     * summary="Retrieve a paginated list of all active companies, with optional name search.",
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Search term for filtering companies by name.",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Company list retrieved successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Company fetched successfully"),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Company"))
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="No companies found."
     * )
     * )
     */
    public function index(Request $request)
    {
        $query = Company::select('id', 'name', 'email');
        $search = $request->query('search');

        if ($search) {
            // Apply search filter to name column (case-insensitive search)
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        // Use pagination for index method (best practice)
        $companies = $query->paginate(15);

        if($companies->isEmpty()){
            return response()->json(ApiResponse::error('No company found', null, 404)->toArray(), 404);
        }

        return response()->json(ApiResponse::success('Company fetched successfully', $companies)->toArray(), 200);
    }

    /**
     * @OA\Post(
     * path="/api/company",
     * tags={"Companies"},
     * summary="Create a new company",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email"},
     * @OA\Property(property="name", type="string", example="New Global Corp", maxLength=150),
     * @OA\Property(property="email", type="string", format="email", example="hr@globalcorp.com", maxLength=150)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Company created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Company")
     * )
     * ),
     * @OA\Response(response=422, description="Validation Error (e.g., name or email already exists)"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(Request $request)
    {
        try{
            $validateData = $request->validate([
                'name' => 'required|string|max:150|unique:company,name',
                'email' => 'required|string|email|max:150|unique:company,email',
            ]);
            $company = Company::create($validateData);
            return response()->json(ApiResponse::success('Company created successfully', $company)->toArray(), 201);
        }catch (ValidationException $exception){
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(), 422);
        }catch (\Exception $e){
            return response()->json(ApiResponse::error('Error creating company', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/company/{id}",
     * tags={"Companies"},
     * summary="Retrieve a specific company by ID",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the company to retrieve", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Company retrieved successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Company")
     * )
     * ),
     * @OA\Response(response=404, description="Company not found")
     * )
     */
    public function show(string $id)
    {
        $company = Company::select('id','name','email')->find($id);
        if(!$company){
            return response()->json(ApiResponse::error('Company not found', null, 404)->toArray(), 404);
        }
        return response()->json(ApiResponse::success('Company retrieved successfully', $company)->toArray(), 200);
    }

    /**
     * @OA\Put(
     * path="/api/company/{id}",
     * tags={"Companies"},
     * summary="Update an existing company",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the company to update", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Corp Name", maxLength=150, nullable=true),
     * @OA\Property(property="email", type="string", format="email", example="new.contact@corp.com", maxLength=150, nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Company updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Company")
     * )
     * ),
     * @OA\Response(response=404, description="Company not found"),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(Request $request, string $id)
    {
        $company = Company::find($id);
        if(!$company){
            return response()->json(ApiResponse::error('Company not found', null, 404)->toArray(), 404);
        }
        try{
            $validateData = $request->validate([
                'name' => 'sometimes|required|string|max:150|unique:company,name,'.$company->id,
                'email' => 'sometimes|required|string|email|max:150|unique:company,email,'.$company->id,
            ]);
            $company->update($validateData);
            return response()->json(ApiResponse::success('Company updated successfully', $company)->toArray(), 200);
        }catch (ValidationException $exception){
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(), 422);
        }catch (\Exception $e){
            return response()->json(ApiResponse::error('Error updating company', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/company/{id}",
     * tags={"Companies"},
     * summary="Delete a company (Soft Delete)",
     * @OA\Parameter(name="id", in="path", required=true, description="ID of the company to delete", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Company deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Company deleted successfully.")
     * )
     * ),
     * @OA\Response(response=404, description="Company not found"),
     * @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(string $id)
    {
        try{
            $company = Company::find($id);
            if(!$company){
                return response()->json(ApiResponse::error('Company not found', null, 404)->toArray(), 404);
            }
            $company->delete();
            return response()->json(ApiResponse::success('Company deleted successfully')->toArray(), 200);
        }catch (\Exception $e){
            return response()->json(ApiResponse::error('Error deleting company', [$e->getMessage()])->toArray(), 500);
        }
    }
}
