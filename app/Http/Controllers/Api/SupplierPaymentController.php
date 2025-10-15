<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 * name="Supplier Payments (Bills)",
 * description="API Endpoints for managing supplier bill records."
 * )
 */
class SupplierPaymentController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/supplier-payments",
     * tags={"Supplier Payments (Bills)"},
     * summary="Retrieve a list of all supplier bills.",
     * description="Lists supplier bills with optional filtering by supplier_id and status.",
     * @OA\Parameter(name="supplier_id", in="query", required=false, @OA\Schema(type="integer"), description="Filter by Supplier ID."),
     * @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"unpaid", "partial paid", "paid"}), description="Filter by payment status."),
     * @OA\Response(response=200, description="List of supplier payments retrieved successfully.",
     * @OA\JsonContent(type="object",
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupplierPayment"))
     * )
     * )
     * ),
     * @OA\Response(response=404, description="No bills found.")
     * )
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
     * @OA\Post(
     * path="/api/supplier-payments",
     * tags={"Supplier Payments (Bills)"},
     * summary="Create a new supplier bill record.",
     * description="Creates a new bill, setting due_amount equal to total and status to 'unpaid'. Supports bill image upload.",
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"supplier_id", "total"},
     * @OA\Property(property="supplier_id", type="integer", example=1),
     * @OA\Property(property="total", type="number", format="float", example=15000.00),
     * @OA\Property(property="bill_img", type="string", format="binary", nullable=true, description="Image file of the bill (max 2MB).")
     * )
     * )
     * ),
     * @OA\Response(response=201, description="Bill created successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPayment")
     * ),
     * @OA\Response(response=422, description="Validation Error")
     * )
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
     * @OA\Get(
     * path="/api/supplier-payments/{id}",
     * tags={"Supplier Payments (Bills)"},
     * summary="Retrieve a single supplier bill record.",
     * description="Displays a specific bill, including its associated supplier and payment details.",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Supplier Bill."),
     * @OA\Response(response=200, description="Bill details retrieved successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPayment")
     * ),
     * @OA\Response(response=404, description="Bill not found.")
     * )
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
     * @OA\Put(
     * path="/api/supplier-payments/{id}",
     * tags={"Supplier Payments (Bills)"},
     * summary="Update a supplier bill's core details (Total, Bill Image).",
     * description="Allows editing the bill's total and bill image. Requires POST request with _method=PUT for file uploads.",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Supplier Bill."),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="total", type="number", format="float", example=20000.00, description="New total of the bill."),
     * @OA\Property(property="bill_img", type="string", format="binary", nullable=true, description="New bill image file (will overwrite existing)."),
     * @OA\Property(property="_method", type="string", example="PUT", description="Required for method spoofing.")
     * )
     * )
     * ),
     * @OA\Response(response=200, description="Bill updated successfully.",
     * @OA\JsonContent(ref="#/components/schemas/SupplierPayment")
     * ),
     * @OA\Response(response=404, description="Bill not found."),
     * @OA\Response(response=422, description="Validation Error")
     * )
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
     * @OA\Delete(
     * path="/api/supplier-payments/{id}",
     * tags={"Supplier Payments (Bills)"},
     * summary="Delete a supplier bill record (Soft Delete).",
     * description="Performs a soft delete. The deletion is blocked if the bill has associated payment details.",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID of the Supplier Bill."),
     * @OA\Response(response=200, description="Bill deleted successfully."),
     * @OA\Response(response=404, description="Bill not found."),
     * @OA\Response(response=409, description="Conflict: Cannot delete bill with associated payments.")
     * )
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
