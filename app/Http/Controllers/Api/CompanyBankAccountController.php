<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyBankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = DB::table('company_bank')
            ->select('company_bank.id as pivot_id',
            'company_bank.acc_no',
            'company_bank.branch',
            'company.name as company_name',
            'bank.name as bank_name'
            ) ->join('company', 'company.id', '=', 'company_bank.company_id')
            ->join('bank', 'bank.id', '=', 'company_bank.bank_id')
            ->whereNull('company_bank.deleted_at')
            ->get();

        if($accounts->isEmpty()){
            return response()->json(ApiResponse::error('No active company bank accounts found.', null)->toArray(), 404);
        }
        return response()->json(ApiResponse::success('Company bank accounts retrieved successfully.', $accounts)->toArray(), 200);
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $updated = DB::table('company_bank')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now(),
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                return response()->json(ApiResponse::error('Company bank account link not found or already unlinked', null)->toArray(), 404);
            }
            return response()->json(ApiResponse::success('Account unlinked successfully')->toArray(), 200);

        }catch(\Exception $e){
                return response()->json(ApiResponse::error('Error unlinking account', [$e->getMessage()])->toArray(), 500);
        }

    }
}
