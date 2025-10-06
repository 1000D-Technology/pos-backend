<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Company Bank Accounts",
 *     description="Manage company bank account relationships"
 * )
 */
class CompanyBankAccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/company-bank-accounts",
     *     summary="List all active company bank accounts",
     *     description="Retrieve a list of all active company bank account associations.",
     *     operationId="getCompanyBankAccounts",
     *     tags={"Company Bank Accounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Company bank accounts retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company bank accounts retrieved successfully."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="pivot_id", type="integer", example=1),
     *                     @OA\Property(property="acc_no", type="string", example="1234567890"),
     *                     @OA\Property(property="branch", type="string", example="Colombo Main"),
     *                     @OA\Property(property="company_name", type="string", example="ABC Pvt Ltd"),
     *                     @OA\Property(property="bank_name", type="string", example="Bank of Ceylon")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active company bank accounts found."
     *     )
     * )
     */
    public function index()
    {
        $accounts = DB::table('company_bank')
            ->select(
                'company_bank.id as pivot_id',
                'company_bank.acc_no',
                'company_bank.branch',
                'company.name as company_name',
                'bank.name as bank_name'
            )
            ->join('company', 'company.id', '=', 'company_bank.company_id')
            ->join('bank', 'bank.id', '=', 'company_bank.bank_id')
            ->whereNull('company_bank.deleted_at')
            ->get();

        if ($accounts->isEmpty()) {
            return response()->json(ApiResponse::error('No active company bank accounts found.', null)->toArray(), 404);
        }

        return response()->json(ApiResponse::success('Company bank accounts retrieved successfully.', $accounts)->toArray(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/company-bank-accounts",
     *     summary="Link a company to a bank account",
     *     description="Create a new company-bank account association.",
     *     operationId="storeCompanyBankAccount",
     *     tags={"Company Bank Accounts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id","bank_id","acc_no","branch"},
     *             @OA\Property(property="company_id", type="integer", example=1),
     *             @OA\Property(property="bank_id", type="integer", example=2),
     *             @OA\Property(property="acc_no", type="string", example="1234567890"),
     *             @OA\Property(property="branch", type="string", example="Kandy Branch")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account linked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account linked successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error linking account"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:company,id',
                'bank_id' => 'required|exists:bank,id',
                'acc_no' => 'required|string|max:50|unique:company_bank,acc_no',
                'branch' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validatedData = $validator->validated();

            $company = Company::find($validatedData['company_id']);

            $company->banks()->attach($validatedData['bank_id'], [
                'acc_no' => $validatedData['acc_no'],
                'branch' => $validatedData['branch'],
            ]);

            return response()->json(
                ApiResponse::success('Account linked successfully.')->toArray(),
                201
            );

        } catch (ValidationException $exception) {
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(), 422);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error linking account', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/company-bank-accounts/{id}",
     *     summary="Unlink a company bank account",
     *     description="Soft delete a company-bank account association by marking it as deleted.",
     *     operationId="deleteCompanyBankAccount",
     *     tags={"Company Bank Accounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Pivot ID of the company-bank account link",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account unlinked successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company bank account link not found or already unlinked"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error unlinking account"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $updated = DB::table('company_bank')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                return response()->json(ApiResponse::error('Company bank account link not found or already unlinked', null)->toArray(), 404);
            }

            return response()->json(ApiResponse::success('Account unlinked successfully')->toArray(), 200);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error unlinking account', [$e->getMessage()])->toArray(), 500);
        }
    }
}
