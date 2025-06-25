<?php

namespace App\Services;

use App\Models\Livestock;
use App\Models\OrderOfPayment;
use App\Models\Owner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;

/**
 * Class OrderOfPaymentService.
 */
class OrderOfPaymentService extends BaseService
{

    private $paymentService;
    private $baseService;
    private $accountCodeService;

    public function __construct(PaymentService $paymentService, BaseService $baseService, AccountCodeService $accountCodeService)
    {
        $this->model = new OrderOfPayment();
        $this->paymentService = $paymentService;
        $this->baseService = $baseService;
        $this->accountCodeService = $accountCodeService;
    }


    private function generateOPNumber()
    {
        $now = Carbon::now();

        $year = $now->format('y');
        $month = $now->format('m');
        $day = $now->format('d');
        $milliseconds = substr((string) microtime(true), -3);

        $opNumber = 'CLASS-'.$year.$month.$day.$milliseconds;
        return $opNumber;
    }

    public function generatePdf($payload)
    {
        $payload = $this->reviewOP($payload);

        $payload['op_no'] = $this->generateOPNumber();

        // $pops_payment = $this->paymentService->createPayment((object) [
        //     'oprefid' => $payload['op_no'],
        //     'name' => $payload['owner_name'],
        //     'address' => $payload['owner_address'],
        //     'postedby' => auth()->user()->first_name.' '.auth()->user()->last_name,
        //     'duedate' => now()->addMonth()->format('Y-m-d'),
        //     'items' => array_map(function ($item) {
        //                 return [
        //                     'accountcode' => $item['code'],
        //                     'amount' => number_format((float) $item['amount'], 2, '.', ''),
        //                 ];
        //             }, $payload['account_code_details'])           
        // ]);

        // if ($pops_payment != 'Success') {
        //     return response()->json(['message' => config('services.pops_error')], 400);
        // }
        
        $order_of_payment = $this->create($payload);
        
        $livestock_update_payload = [
            'status' => 'OP Generated',
            'order_of_payment_id' =>  $order_of_payment->id,
        ];

        foreach ($payload->livestock as $livestock) {
            $this->baseService
            ->setModel(new Livestock())
            ->updateById($livestock['id'], $livestock_update_payload);
        }

        $orderOfPayment = $this->generatePdfBlade($payload);

        return $orderOfPayment;
    }

    public function reviewOP($payload)
    {
        if ($this->paymentService->connect() === 'Failed.') {
            Notification::make()
                ->alignment(Alignment::Center)
                ->title('Error')
                ->danger()
                ->body('Connection failed.')
                ->send();

            return null; 
        }

        $checkPayload = (object) [
            'owner_id' => $payload->owner_id,
            'batch' => $payload->livestock[0]['batch'],
        ];

        if ($payload->purpose == 'slaughter') {
            if ($this->checkDuplicateOP($checkPayload)) {
               Notification::make()
                    ->title('Duplicate OP')
                    ->danger()
                    ->body('An existing for slaughter OP for this owner batch already exists.')
                    ->persistent()
                    ->send();

            return null; 
            }
        }

        $owner = $this->baseService->setModel(new Owner())->findById($payload->owner_id);


        $owner_name = collect([
            $owner?->first_name,
            $owner?->middle_name,
            $owner?->last_name
        ])->filter()->implode(' ');

        $livestockType = strtolower($payload->livestock[0]['type']);
        $totalAmount = 0;

        $accountDetails = [];

        foreach ($payload->account_codes as $code => $label) {
            $account_code = $this->accountCodeService->findByAccountCode($code);

            $amount = match ($livestockType) {
                'hog' => $account_code->hog_amount ?? $account_code->amount,
                'cattle' => $account_code->cattle_amount ?? $account_code->amount,
                'goat' => $account_code->goat_amount ?? $account_code->amount,
                default => $account_code->amount,
            };

            $amount = (float) $amount * count($payload->livestock);

            $accountDetails[] = [
                'code' => $code,
                'description' => $label,
                'amount' => $amount,
            ];

            $totalAmount += $amount;
        }
    
        $payload->account_code_details = $accountDetails;
        $payload->owner_name = $owner_name;
        $payload->owner_address = $owner->address;
        $payload->total_amount = $totalAmount;
        $payload->encoded_by = auth()->user()->id;

        return $payload;
    }

    public function create(object $payload)
    {
        $orderOfPayment = new OrderOfPayment();
        $orderOfPayment->encoded_by = Auth::user()->id;
        $orderOfPayment->order_of_payment_no = $payload->op_no;
        $orderOfPayment->account_codes = json_encode(array_keys((array) $payload->account_codes));
        $orderOfPayment->owner_id = $payload->owner_id;
        $orderOfPayment->batch = $payload->livestock[0]['batch'];
        $orderOfPayment->status = 'Awaiting payment';
        $orderOfPayment->amount = $payload->total_amount;
        $orderOfPayment->remarks = $payload->remarks;
        $orderOfPayment->purpose = $payload->purpose;

        $orderOfPayment->save();
        return $orderOfPayment->fresh();
    }

    public function generatePdfBlade(object $payload)
    {
        try {
            $pdf = Pdf::loadView('order-of-payment', [
                'owner_name' => $payload->owner_name,
                'owner_address' => $payload->owner_address,
                'account_code_details' => $payload->account_code_details,
                'total_amount' => $payload->total_amount,
                'op_number' => $payload->op_no,
                'op_sys' => 'CLASS',
                'remarks' => $payload->remarks,
                'valid_until_date' => now()->addDays(7)->format('F d, Y'),
            ]);

            return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');

        } catch (\Throwable $e) {
            return null;
        }
    }

    public function checkDuplicateOP(object $payload)
    {
        $existingOP = OrderOfPayment::where('owner_id', $payload->owner_id)
            ->where('batch', $payload->batch)
            ->where('purpose', 'slaughter')
            ->first();
        
        return $existingOP;
    }



}
