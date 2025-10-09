<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupplierPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupplierPayment::query();
        if($request->has('supplier_id')){
            $query->where('supplier_id',$request->supplier_id);
        }
        if($request->has('status')){
            $query->where('status', $request->status);
        }
        $payments = $query->with('supplier:id,name')->paginate(10);

        if($payments->isEmpty()){
            return response()->json(ApiResponse::error('No supplier bills found')->toArray(), 404);
        }
        return response()->json(ApiResponse::success('Supplier bills retrieved successfully', $payments)->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'total' => 'required|numeric|min:0.01',
                'bill_img' => 'nullable|image|max:2048',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $validateData = $validator->validated();
            $validateData['due_amount'] = $validateData['total'];
            $validateData['status'] = 'unpaid';

            if($request->hasFile('bill_img')){
                $path = $request->file('bill_img')->store('public/supplier_bills');
                $validateData['bill_img'] = Storage::url($path);
            }

            $payment = SupplierPayment::create($validateData);
            return response()->json(ApiResponse::success('Supplier bill created successfully', $payment)->toArray(), 201);
        }catch (ValidationException $exception){
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(), 422);
        }catch (\Exception $exception){
            return response()->json(ApiResponse::error('Server Error', [$exception->getMessage()])->toArray(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = SupplierPayment::with(['supplier','details'])->find($id);
        if(!$payment){
            return response()->json(ApiResponse::error('Supplier bill not found')->toArray(), 404);
        }
        return response()->json(ApiResponse::success('Supplier bill retrieved successfully', $payment)->toArray(), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $payment = SupplierPayment::find($id);
            if (!$payment) {
                return response()->json(ApiResponse::error('Supplier bill not found.', null, 404)->toArray(), 404);
            }

            $validator = Validator::make($request->all(), [
                'total' => 'sometimes|required|numeric|min:0.01',
                'bill_img' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validatedData = $validator->validated();

            if ($request->hasFile('bill_img')) {
                $publicPath = str_replace('/storage/', 'public/', $payment->bill_img);

                if ($payment->bill_img && Storage::exists($publicPath)) {
                    Storage::delete($publicPath);
                }

                $path = $request->file('bill_img')->store('public/supplier_bills');
                $validatedData['bill_img'] = Storage::url($path);
            }

            if (isset($validatedData['total']) && $validatedData['total'] != $payment->total) {
                $oldPaidAmount = $payment->total - $payment->due_amount;
                $newDueAmount = $validatedData['total'] - $oldPaidAmount;

                $validatedData['due_amount'] = max(0, $newDueAmount);

                if ($validatedData['due_amount'] <= 0) {
                    $validatedData['status'] = 'paid';
                } elseif ($oldPaidAmount > 0) {
                    $validatedData['status'] = 'partial paid';
                } else {
                    $validatedData['status'] = 'unpaid';
                }
            }

            $payment->update($validatedData);

            return response()->json(ApiResponse::success('Bill updated successfully.', $payment)->toArray(), 200);

        } catch (ValidationException $exception) {
            return response()->json(ApiResponse::error('Validation Error', $exception->errors())->toArray(), 422);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error updating supplier bill', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $payment = SupplierPayment::find($id);
            if(!$payment){
                return response()->json(ApiResponse::error('Supplier bill not found')->toArray(), 404);
            }

            if($payment->details()->count() > 0){
                return response()->json(ApiResponse::error('Cannot delete bill with associated payment details', ['This bill has associated payment details and must be retained for audit purposes.'])->toArray(), 409);
            }

            $payment->delete();
            return response()->json(ApiResponse::success('Supplier bill deleted successfully')->toArray(), 200);
        }catch(\Exception $exception){
            return response()->json(ApiResponse::error('Error deleting supplier bill',[$exception->getMessage()])->toArray(), 500);
        }
    }

}
