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


/**
 * @OA\Tag(
 * name="Supplier Payment Details",
 * description="API Endpoints for managing individual payment transactions linked to a bill."
 * )
 */
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
     * @OA\Get(
     * path="/api/supplier-payments/{payment_id}/details",
     * tags={"Supplier Payment Details"},
     * summary="List all payment details for a specific supplier bill.",
     * @OA\Parameter(name="payment_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the parent Supplier Bill."),
     * @OA\Response(response=200, description="List of payment details retrieved successfully.",
     * @OA\JsonContent(type="object",
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupplierPaymentDetail"))
     * )
     * )
     * ),
     * @OA\Response(response=404, description="Parent bill not found.")
     * )
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
     * @OA\Post(
     * path="/api/supplier-payments/{payment_id}/details",
     * tags={"Supplier Payment Details"},
     * summary="Create a new payment transaction against a bill.",
     * description="Creates a new payment detail, updates parent bill's due_amount and status transactionally.",
     * @OA\Parameter(name="payment_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the parent Supplier Bill."),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"paid_amount", "payment_method"},
     * @OA\Property(property="paid_amount", type="number", format="float", example=5000.00),
     * @OA\Property(property="payment_method", type="string", enum={"Cash", "Bank Transfer", "Cheque", "Credit Card"}, example="Cash"),
     * @OA\Property(property="note", type="string", nullable=true),
     * @OA\Property(property="img", type="string", format="binary", nullable=true, description="Payment receipt image (max 2MB).")
     * )
     * )
     * ),
     * @OA\Response(response=201, description="Payment detail created successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPaymentDetail")
     * ),
     * @OA\Response(response=422, description="Validation Error or Payment exceeds due amount."),
     * @OA\Response(response=404, description="Parent bill not found.")
     * )
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
     * @OA\Get(
     * path="/api/supplier-payments/{payment_id}/details/{detail_id}",
     * tags={"Supplier Payment Details"},
     * summary="Retrieve a single payment detail record.",
     * @OA\Parameter(name="payment_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the parent Supplier Bill."),
     * @OA\Parameter(name="detail_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Payment Detail."),
     * @OA\Response(response=200, description="Payment detail retrieved successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPaymentDetail")
     * ),
     * @OA\Response(response=404, description="Payment detail not found.")
     * )
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
     * @OA\Put(
     * path="/api/supplier-payments/{payment_id}/details/{detail_id}",
     * tags={"Supplier Payment Details"},
     * summary="Update a payment transaction.",
     * description="Updates a detail record and correctly recalculates the parent bill's due_amount and status transactionally. Requires POST request with _method=PUT for file uploads.",
     * @OA\Parameter(name="payment_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the parent Supplier Bill."),
     * @OA\Parameter(name="detail_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Payment Detail."),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="paid_amount", type="number", format="float", example=6000.00, description="New paid amount."),
     * @OA\Property(property="payment_method", type="string", enum={"Cash", "Bank Transfer", "Cheque", "Credit Card"}, example="Cheque"),
     * @OA\Property(property="note", type="string", nullable=true),
     * @OA\Property(property="img", type="string", format="binary", nullable=true, description="New payment receipt image (optional)."),
     * @OA\Property(property="_method", type="string", example="PUT", description="Required for method spoofing.")
     * )
     * )
     * ),
     * @OA\Response(response=200, description="Payment detail updated successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPaymentDetail")
     * ),
     * @OA\Response(response=404, description="Payment detail or parent bill not found."),
     * @OA\Response(response=422, description="Validation Error or New payment exceeds due amount.")
     * )
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
     * @OA\Delete(
     * path="/api/supplier-payments/{payment_id}/details/{detail_id}",
     * tags={"Supplier Payment Details"},
     * summary="Delete a payment transaction.",
     * description="Deletes a detail record and correctly recalculates the parent bill's due_amount and status transactionally.",
     * @OA\Parameter(name="payment_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the parent Supplier Bill."),
     * @OA\Parameter(name="detail_id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Payment Detail."),
     * @OA\Response(response=200, description="Payment detail deleted successfully."),
     * @OA\Response(response=404, description="Payment detail or parent bill not found.")
     * )
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
