<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupplierPaymentDetailsController extends Controller
{

    private const ALLOWED_PAYMENT_METHODS = ['Cash', 'Bank Transfer', 'Cheque', 'Credit Card'];

    private function updateBillStatus(SupplierPayment $bill): void
    {
        $bill->due_amount = max(0, $bill->due_amount);

        if ($bill->due_amount <= 0) {
            $bill->status = 'paid';
        } else {
            $totalPaid = $bill->total - $bill->due_amount;
            if ($totalPaid > 0) {
                $bill->status = 'partial paid';
            } else {
                $bill->status = 'unpaid';
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $payment_id)
    {
        $bill = SupplierPayment::find($payment_id);
        if(!$bill){
            return response()->json(ApiResponse::error('Parent Supplier Bill not found' , null)->toArray(),404);
        }
        $details = $bill->details()->paginate(15);
        return response()->json(ApiResponse::success('Payment details retrieved successfully.',$details)->toArray(),200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request ,string $payment_id)
    {
        try{
            $bill = SupplierPayment::find($payment_id);
            if(!$bill){
               return response()->json(ApiResponse::error('Parent Supplier Bill not found' , null)->toArray(),404);
            }
            $validator = Validator::make($request->all(),[
               'paid_amount' => 'required|numeric|min:0.01',
                'payment_method' => ['required', Rule::in(self::ALLOWED_PAYMENT_METHODS)],
                'note' => 'nullable|string|max:255',
                'img' => 'nullable|image|max:2048'
            ]);
            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $validatedData = $validator->validated();
            $validatedData['supplier_payment_id'] = $payment_id;

            if($validatedData['paid_amount'] > $bill->due_amount){
                return response()->json(ApiResponse::error('Payment exceeds due amount.', ['paid_amount' => 'The paid amount cannot be greater than the current due amount of ' . $bill->due_amount])->toArray(),422);
            }
            DB::beginTransaction();

            if($request->hasFile('img')){
                $path = $request->file('img')->store('public/payment_proofs');
                $validatedData['img'] = Storage::url($path);
            }

            $detail = SupplierPaymentDetail::create($validatedData);
            $bill->due_amount -= $validatedData['paid_amount'];
            $this->updateBillStatus($bill);
            $bill->save();
            DB::commit();
            return response()->json(ApiResponse::success('Payment detail added successfully.',$detail)->toArray(),201);
        }catch(ValidationException $e){
            DB::rollBack();
            return response()->json(ApiResponse::error('Validation Error',$e->errors())->toArray(),422);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(ApiResponse::error('An error occurred while processing the request.', [$e->getMessage()])->toArray(),500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $payment_id, string $detail_id)
    {
        $detail = SupplierPaymentDetail::where('supplier_payment_id', $payment_id)->find($detail_id);
        if(!$detail){
            return response()->json(ApiResponse::error('Payment detail not found' , null)->toArray(),404);
        }
        return response()->json(ApiResponse::success('Payment detail retrieved successfully.',$detail)->toArray(),200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $payment_id, string $detail_id)
    {
        try{
            $detail = SupplierPaymentDetail::where('supplier_payment_id', $payment_id)->find($detail_id);
            if(!$detail){
                return response()->json(ApiResponse::error('Payment detail not found.',null)->toArray(),404);
            }
            $bill = $detail->supplierPayment;
            if(!$bill){
                return response()->json(ApiResponse::error('Parent Supplier Bill not found' , null)->toArray(),404);
            }
            $validator = Validator::make($request->all(),[
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_method' => ['sometimes', Rule::in(self::ALLOWED_PAYMENT_METHODS)],
                'note' => 'nullable|string|max:255',
                'img' => 'nullable|image|max:2048'
            ]);
            if($validator->fails()){
                throw new ValidationException($validator);
            }
            $validatedData = $validator->validated();
            $originalPaidAmount = $detail->paid_amount;
            $newPaidAmount = $validatedData['paid_amount'] ?? $originalPaidAmount;

            $dueAfterRevert = $bill->due_amount + $originalPaidAmount;
            if($newPaidAmount > $dueAfterRevert){
                return response()->json(ApiResponse::error('New payment exceeds due amount.',
                    ['paid_amount' => 'The new paid amount cannot be greater than the current due amount (' . $dueAfterRevert . ')'])->toArray(),422);
            }

            DB::beginTransaction();
            if($request->hasFile('img')){
                if($detail->img){
                    Storage::delete(str_replace('storage','public',$detail->img));
                }
                $path = $request->file('img')->store('public/payment_proofs');
                $validatedData['img'] = Storage::url($path);
            }
            $bill->due_amount = $dueAfterRevert - $newPaidAmount;

            $detail->update($validatedData);

            $this->updateBillStatus($bill);
            $bill->save();
            DB::commit();

            return response()->json(ApiResponse::success('Payment detail updated successfully.',$detail)->toArray(),200);
        }catch (ValidationException $exception){
            DB::rollBack();
            return response()->json(ApiResponse::error('Validation Error',$exception->errors())->toArray(),422);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(ApiResponse::error('An error occurred while processing the request.', [$e->getMessage()])->toArray(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $payment_id, string $detail_id)
    {
        try{
         $detail = SupplierPaymentDetail::where('supplier_payment_id',  $payment_id)->find($detail_id);
            if(!$detail){
                return response()->json(ApiResponse::error('Payment detail not found.',null)->toArray(),404);
            }
            $bill = $detail->supplierPayment;
            if(!$bill){
                return response()->json(ApiResponse::error('Parent Supplier Bill not found' , null)->toArray(),404);
            }
            $revertedAmount = $detail->paid_amount;
            DB::beginTransaction();
            if($detail->img){
                Storage::delete(str_replace('storage','public',$detail->img));
            }
            $detail->delete();
            $bill->due_amount += $revertedAmount;
            $this->updateBillStatus($bill);
            $bill->save();
            DB::commit();
            return response()->json(ApiResponse::success('Payment detail deleted successfully.',null)->toArray(),200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(ApiResponse::error('An error occurred while processing the request.', [$e->getMessage()])->toArray(),500);
        }
    }
}
