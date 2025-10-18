<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Stock;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 * name="Invoices",
 * description="API Endpoints for Invoice Management"
 * )
 *
 * @OA\Schema(
 * schema="InvoiceStoreRequest",
 * title="Invoice Creation Request",
 * description="Payload required to create a new Invoice transaction.",
 * required={"customer_id", "payments", "items"},
 * @OA\Property(
 * property="customer_id",
 * type="integer",
 * example=1,
 * description="ID of the customer for this invoice."
 * ),
 * @OA\Property(
 * property="invoice_discount",
 * type="number",
 * format="float",
 * example=10.00,
 * nullable=true,
 * description="Overall discount applied to the total invoice amount."
 * ),
 * @OA\Property(
 * property="payments",
 * type="array",
 * minItems=1,
 * description="Array of payments made towards the invoice.",
 * @OA\Items(
 * @OA\Property(property="payment_method", type="string", enum={"Cash", "Credit Card", "Bank Transfer", "Cheque"}),
 * @OA\Property(property="total_given_amount", type="number", format="float", example=1402.50)
 * )
 * ),
 * @OA\Property(
 * property="items",
 * type="array",
 * minItems=1,
 * description="Array of stock items to be included in the invoice.",
 * @OA\Items(
 * @OA\Property(property="stock_id", type="integer", example=10, description="The ID of the stock item."),
 * @OA\Property(property="qty", type="integer", example=2, description="Quantity to be purchased (must be >= 1)."),
 * @OA\Property(property="unit_price", type="number", format="float", example=500.00, description="The sold price per unit."),
 * @OA\Property(property="discount_rate", type="number", format="float", example=0.05, nullable=true, description="Discount rate (0 to 1) applied to the item.")
 * )
 * )
 * )
 */
class InvoiceController extends Controller
{

    private const TAX_RATE = 0.10;
    private const ALLOWED_PAYMENT_METHODS = ['Cash', 'Credit Card', 'Bank Transfer', 'Cheque'];

    /**
     * Display a listing of the resource.
     * @OA\Get(
     * path="/invoices",
     * operationId="getInvoicesList",
     * tags={"Invoices"},
     * summary="Get list of all Invoices, paginated",
     * @OA\Parameter(
     * name="customer_id",
     * in="query",
     * required=false,
     * @OA\Schema(type="integer"),
     * description="Filter by customer ID"
     * ),
     * @OA\Parameter(
     * name="status",
     * in="query",
     * required=false,
     * @OA\Schema(type="string", enum={"pending", "partial_paid", "paid"}),
     * description="Filter by invoice status"
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="Invoices Retrieved Successfully"),
     * @OA\Property(property="data", type="object", ref="#/components/schemas/Invoice")
     * )
     * ),
     * @OA\Response(response=404, description="No Invoices Found")
     * )
     */
    public function index(Request $request)
    {
        $query = Invoice::query();
        if($request->has('customer_id')){
            $query->where('customer_id', $request->customer_id);
        }
        if($request->has('status')){
            $query->where('status', $request->status);
        }
        $invoices = $query->with(['customer:id,name','user:id,name'])->paginate(15);
        if($invoices->isEmpty()){
            return response()->json(ApiResponse::error('No Invoices Found',$request->all())->toArray(),404);
        }
        return response()->json(ApiResponse::success('Invoices Retrieved Successfully',$invoices)->toArray(),200);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     * path="/invoices",
     * operationId="storeInvoice",
     * tags={"Invoices"},
     * summary="Create a new Invoice transaction (Transactional)",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/InvoiceStoreRequest")
     * ),
     * @OA\Response(
     * response=201,
     * description="Invoice created and stock updated successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="Invoice created and stock updated successfully"),
     * @OA\Property(property="data", ref="#/components/schemas/Invoice")
     * )
     * ),
     * @OA\Response(response=400, description="Insufficient Stock"),
     * @OA\Response(response=401, description="Unauthorized Action (User not logged in)"),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Server Error")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_discount' => 'nullable|numeric|min:0', // Overall invoice discount
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => ['required', Rule::in(self::ALLOWED_PAYMENT_METHODS)],
            'payments.*.total_given_amount' => 'required|numeric|min:0.01',
            // Validation for Items received in payload
            'items' => 'required|array|min:1',
            'items.*.stock_id' => 'required|exists:stocks,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.discount_rate' => 'nullable|numeric|between:0,1',
        ]);
        if($validator->fails()){
            return response()->json(ApiResponse::error('Validation Error',$validator->errors())->toArray(),422);
        }
        $userId = auth()->id();
        if(!$userId){
            return response()->json(ApiResponse::error("Unauthorized Action", ['User must be logged in to create an invoice.'])->toArray(),401);
        }

        // Collect items directly from the request payload
        $invoiceItemRequest = collect($request->items);

        DB::beginTransaction();
        try{
            $invoiceDiscount = $request->input('invoice_discount',0.00);
            $totalAmount = 0;
            $totalItemDiscount = 0;
            $stockUpdates = [];
            $invoiceItemData = [];
            $stockIds = $invoiceItemRequest->pluck('stock_id')->toArray();

            // Fetch all necessary stock records upfront and lock them
            $stocks = Stock::whereIn('id', $stockIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($invoiceItemRequest as $item){
                $item = (object) $item;
                $stock = $stocks->get($item->stock_id);
                // Use 'qty' column name as per the Stock model
                if(!$stock || $stock->qty < $item->qty){
                    DB::rollBack();
                    return response()->json(ApiResponse::error('Insufficient Stock', ["Insufficient stock (ID: {$item->stock_id}). Available: " . ($stock ? $stock->qty : 0) . ", Requested: {$item->qty}"])->toArray(), 400);
                }

                // Calculate item totals
                $itemSubtotal = $item->qty * $item->unit_price;
                $itemDiscount = $itemSubtotal * ($item->discount_rate ?? 0);

                $totalAmount += $itemSubtotal;
                $totalItemDiscount += $itemDiscount;

                // Prepare Invoice Item and Stock update data
                $invoiceItemData[] = [
                    'stock_id' => $item->stock_id,
                    'sold_price' => $item->unit_price,
                    'qty' => $item->qty,
                    'discount' => $itemDiscount,
                ];
                $stockUpdates[$item->stock_id] = $item->qty;
            }

            //final invoice calculate
            $subTotalAfterDiscount = $totalAmount - $totalItemDiscount;
            $finalDiscount = $totalItemDiscount + $invoiceDiscount;
            $taxableAmount = max(0, $subTotalAfterDiscount - $invoiceDiscount);

            $tax = $taxableAmount * self::TAX_RATE;
            $grandTotal = $taxableAmount + $tax;

            $totalPaid = collect($request->payments)->sum('total_given_amount');
            $balance = $grandTotal - $totalPaid;

            $status = 'pending';
            if($balance<=0){
                $status = 'paid';
            }elseif ($totalPaid > 0){
                $status = 'partial_paid';
            }

            //create the main invoice
            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'discount' => $finalDiscount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'balance' => max(0, $balance),
                'status' => $status,
            ]);

            foreach ($invoiceItemData as $itemData){
                $itemData['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemData);
                $stockId = $itemData['stock_id'];
                $stocks->get($stockId)->decrement('qty', $stockUpdates[$stockId]);
            }

            foreach ($request->payments as $paymentData){
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method' => $paymentData['payment_method'],
                    'total_given_amount' => $paymentData['total_given_amount'],
                ]);
            }
            DB::commit();
            return response()->json(ApiResponse::success('Invoice created and stock updated successfully',$invoice)->toArray(),201);

        }catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(ApiResponse::error('Invoice Creation Failed', $e->errors())->toArray(), 422);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(ApiResponse::error('Invoice Creation Failed', [$e->getMessage()])->toArray(), 500);
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
        //
    }
}
