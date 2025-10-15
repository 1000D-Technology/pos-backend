<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Salary Payments",
 *     description="API endpoints for managing salary payments"
 * )
 */
class SalaryPaymentController extends Controller
{
    /**
     * Display a listing of salary payments.
     *
     * @OA\Get(
     *     path="/api/salary-payments",
     *     operationId="getSalaryPayments",
     *     tags={"Salary Payments"},
     *     summary="Get list of salary payments",
     *     description="Returns paginated list of salary payments with optional filters. Salary records include calculated total_paid and balance fields.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="salary_id",
     *         in="query",
     *         description="Filter by salary ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="salary_paid_by",
     *         in="query",
     *         description="Filter by user who made the payment",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="payment_type",
     *         in="query",
     *         description="Filter by payment type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"regular", "advance", "bonus", "overtime", "commission", "allowance", "adjustment"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="salary_id", type="integer", example=1),
     *                         @OA\Property(property="salary_paid_by", type="integer", example=1),
     *                         @OA\Property(property="payment_type", type="string", enum={"regular", "advance", "bonus", "overtime", "commission", "allowance", "adjustment"}, example="regular"),
     *                         @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *                         @OA\Property(property="paid_amount", type="number", format="float", example=50000.00),
     *                         @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08"),
     *                         @OA\Property(property="payment_note", type="string", example="Monthly salary payment"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="salary",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="salary_month", type="string", example="2025-10"),
     *                             @OA\Property(property="total_salary", type="number", format="float", example=53000.00),
     *                             @OA\Property(property="total_paid", type="number", format="float", example=30000.00, description="Total amount paid so far"),
     *                             @OA\Property(property="balance", type="number", format="float", example=23000.00, description="Remaining balance to be paid"),
     *                             @OA\Property(
     *                                 property="user",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="John Doe"),
     *                                 @OA\Property(property="email", type="string", example="john@example.com")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="paid_by",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="Admin User"),
     *                             @OA\Property(property="email", type="string", example="admin@example.com")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = SalaryPayment::with(['salary.user:id,name,email', 'paidBy:id,name,email']);

        // Filter by salary_id if provided
        if ($request->has('salary_id')) {
            $query->where('salary_id', $request->salary_id);
        }

        // Filter by salary_paid_by if provided
        if ($request->has('salary_paid_by')) {
            $query->where('salary_paid_by', $request->salary_paid_by);
        }

        // Filter by payment_type if provided
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ], 200);
    }

    /**
     * Store a newly created salary payment.
     *
     * @OA\Post(
     *     path="/api/salary-payments",
     *     operationId="storeSalaryPayment",
     *     tags={"Salary Payments"},
     *     summary="Record a new salary payment",
     *     description="Create a new salary payment record with specified type (regular, advance, bonus, etc.)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"salary_id","salary_paid_by","paid_amount"},
     *             @OA\Property(property="salary_id", type="integer", example=1, description="Salary slip ID"),
     *             @OA\Property(property="salary_paid_by", type="integer", example=1, description="User ID who made the payment"),
     *             @OA\Property(
     *                 property="payment_type", 
     *                 type="string", 
     *                 enum={"regular", "advance", "bonus", "overtime", "commission", "allowance", "adjustment"},
     *                 example="regular", 
     *                 description="Type of payment (default: regular)"
     *             ),
     *             @OA\Property(property="payment_method", type="string", example="Bank Transfer", description="Payment method used"),
     *             @OA\Property(property="paid_amount", type="number", format="float", example=50000.00, description="Amount paid"),
     *             @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08", description="Date of payment"),
     *             @OA\Property(property="payment_note", type="string", example="Monthly salary payment", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Salary payment recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Salary payment recorded successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="salary_id", type="integer", example=1),
     *                 @OA\Property(property="salary_paid_by", type="integer", example=1),
     *                 @OA\Property(property="payment_type", type="string", example="regular"),
     *                 @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *                 @OA\Property(property="paid_amount", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08"),
     *                 @OA\Property(property="payment_note", type="string", example="Monthly salary payment"),
     *                 @OA\Property(
     *                     property="salary",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="total_paid", type="number", format="float", example=50000.00),
     *                     @OA\Property(property="balance", type="number", format="float", example=3000.00),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="paid_by",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Admin User"),
     *                     @OA\Property(property="email", type="string", example="admin@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary record not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="paid_amount",
     *                     type="array",
     *                     @OA\Items(type="string", example="The paid amount field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salary_id' => 'required|exists:salaries,id',
            'salary_paid_by' => 'required|exists:users,id',
            'payment_type' => 'nullable|string|in:regular,advance,bonus,overtime,commission,allowance,adjustment',
            'payment_method' => 'nullable|string|max:255',
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_note' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $salary = Salary::find($request->salary_id);

        if (!$salary) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary record not found.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Create payment
            $payment = SalaryPayment::create([
                'salary_id' => $salary->id,
                'salary_paid_by' => $request->salary_paid_by,
                'payment_type' => $request->payment_type ?? 'regular',
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date ?? now()->format('Y-m-d'),
                'payment_note' => $request->payment_note,
            ]);

            $payment->load(['salary.user:id,name,email', 'paidBy:id,name,email']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Salary payment recorded successfully.',
                'data' => $payment,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record salary payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified salary payment.
     *
     * @OA\Get(
     *     path="/api/salary-payments/{id}",
     *     operationId="getSalaryPayment",
     *     tags={"Salary Payments"},
     *     summary="Get salary payment details",
     *     description="Returns details of a specific salary payment including total_paid and balance for the salary",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Salary payment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="salary_id", type="integer", example=1),
     *                 @OA\Property(property="salary_paid_by", type="integer", example=1),
     *                 @OA\Property(property="payment_type", type="string", example="regular"),
     *                 @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *                 @OA\Property(property="paid_amount", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08"),
     *                 @OA\Property(property="payment_note", type="string", example="Monthly salary payment"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="salary",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="total_paid", type="number", format="float", example=50000.00),
     *                     @OA\Property(property="balance", type="number", format="float", example=3000.00),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="paid_by",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Admin User"),
     *                     @OA\Property(property="email", type="string", example="admin@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary payment not found.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $payment = SalaryPayment::with(['salary.user:id,name,email', 'paidBy:id,name,email'])->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary payment not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payment,
        ], 200);
    }

    /**
     * Update the specified salary payment.
     *
     * @OA\Put(
     *     path="/api/salary-payments/{id}",
     *     operationId="updateSalaryPayment",
     *     tags={"Salary Payments"},
     *     summary="Update salary payment",
     *     description="Update an existing salary payment record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Salary payment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="payment_type", 
     *                 type="string", 
     *                 enum={"regular", "advance", "bonus", "overtime", "commission", "allowance", "adjustment"},
     *                 example="bonus", 
     *                 description="Type of payment"
     *             ),
     *             @OA\Property(property="payment_method", type="string", example="Cash", description="Payment method used"),
     *             @OA\Property(property="paid_amount", type="number", format="float", example=50000.00, description="Amount paid"),
     *             @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08", description="Date of payment"),
     *             @OA\Property(property="payment_note", type="string", example="Updated payment note", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salary payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Salary payment updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="salary_id", type="integer", example=1),
     *                 @OA\Property(property="salary_paid_by", type="integer", example=1),
     *                 @OA\Property(property="payment_type", type="string", example="bonus"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash"),
     *                 @OA\Property(property="paid_amount", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="payment_date", type="string", format="date", example="2025-10-08"),
     *                 @OA\Property(property="payment_note", type="string", example="Updated payment note")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary payment not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $payment = SalaryPayment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary payment not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'payment_type' => 'nullable|string|in:regular,advance,bonus,overtime,commission,allowance,adjustment',
            'payment_method' => 'nullable|string|max:255',
            'paid_amount' => 'nullable|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_note' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment->update($request->only([
                'payment_type',
                'payment_method',
                'paid_amount',
                'payment_date',
                'payment_note',
            ]));

            $payment->load(['salary.user:id,name,email', 'paidBy:id,name,email']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Salary payment updated successfully.',
                'data' => $payment,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update salary payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified salary payment.
     *
     * @OA\Delete(
     *     path="/api/salary-payments/{id}",
     *     operationId="deleteSalaryPayment",
     *     tags={"Salary Payments"},
     *     summary="Delete salary payment",
     *     description="Delete a salary payment record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Salary payment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salary payment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Salary payment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary payment not found.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $payment = SalaryPayment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary payment not found.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $payment->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Salary payment deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete salary payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
