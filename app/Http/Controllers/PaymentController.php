<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle return redirect from Paystack after customer completes checkout or cancels.
     */
    public function callback(Request $request, PaystackService $paystack)
    {
        $reference = $request->query('reference') ?: $request->query('trxref');

        if (empty($reference)) {
            return redirect()->route('products.index')
                ->with('error', 'No payment reference was received from Paystack.');
        }

        // Resolve sale early from reference pattern if available
        $saleId = null;
        if (preg_match('/ATH-PAY-(\d+)-/', $reference, $matches)) {
            $saleId = (int) $matches[1];
        }
        $sale = $saleId ? Sale::find($saleId) : null;

        $verification = $paystack->verifyTransaction($reference);

        if (empty($verification['status']) || empty($verification['data'])) {
            $msg = $verification['message'] ?? 'Could not verify payment with Paystack.';

            if ($sale && $sale->payment_status === PaymentStatus::Unpaid) {
                $sale->status = SaleStatus::Cancelled;
                $prefix = $sale->internal_notes ? $sale->internal_notes . "\n" : '';
                $sale->internal_notes = $prefix . "Paystack verification unsuccessful: {$msg} (Ref: {$reference})";
                $sale->saveQuietly();

                \App\Models\AuditLog::record(
                    action: 'payment_cancelled',
                    description: "Online Customer ({$sale->customer_name}) cancelled Paystack checkout or verification failed: {$msg}",
                    auditable: $sale,
                    newValues: ['status' => SaleStatus::Cancelled->value, 'reason' => $msg]
                );
            }

            return redirect()->route('products.index')
                ->with('error', $msg);
        }

        $data = $verification['data'];

        // If metadata has sale_id and we haven't resolved yet
        if (! $sale && ! empty($data['metadata']['sale_id'])) {
            $sale = Sale::find($data['metadata']['sale_id']);
        }

        if (($data['status'] ?? '') !== 'success') {
            $msg = $data['gateway_response'] ?? ($data['message'] ?? 'Payment was cancelled or unsuccessful.');

            if ($sale && $sale->payment_status === PaymentStatus::Unpaid) {
                $sale->status = SaleStatus::Cancelled;
                $prefix = $sale->internal_notes ? $sale->internal_notes . "\n" : '';
                $sale->internal_notes = $prefix . "Customer cancelled or abandoned Paystack checkout: {$msg} (Ref: {$reference})";
                $sale->saveQuietly();

                \App\Models\AuditLog::record(
                    action: 'payment_cancelled',
                    description: "Online Customer ({$sale->customer_name}) cancelled or failed Paystack checkout ({$msg})",
                    auditable: $sale,
                    newValues: ['status' => SaleStatus::Cancelled->value, 'gateway_response' => $msg]
                );
            }

            return redirect()->route('products.index')
                ->with('error', "Paystack payment: {$msg}");
        }

        if (! $sale) {
            Log::error('Paystack verified payment but sale was not found', ['reference' => $reference, 'data' => $data]);
            return redirect()->route('products.index')
                ->with('error', 'Payment received, but associated order could not be located. Please contact our support team.');
        }

        // Idempotent payment recording
        $existingPayment = SalePayment::where('reference', $reference)->first();

        if (! $existingPayment) {
            $amountInNaira = round(((float) $data['amount']) / 100, 2);
            $channel = $data['channel'] ?? 'card';

            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'amount' => $amountInNaira,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'paystack',
                'reference' => $reference,
                'notes' => "Online payment verified via Paystack ({$channel})",
            ]);

            $sale->status = SaleStatus::Processing;
            $sale->paid_at = now();
            $sale->recalculateTotals(save: true);

            $this->sendReceiptNotification($sale, $payment);
        }

        return redirect()->route('payment.success', [
            'sale' => $sale->id,
            'reference' => $reference,
        ]);
    }

    /**
     * Show payment confirmation & order receipt.
     */
    public function success(Request $request, Sale $sale)
    {
        $reference = $request->query('reference');

        // Security check: Must have a verified Paystack reference belonging to this sale,
        // OR a valid cryptographic signature, OR an authenticated authorized staff user.
        $payment = $reference ? $sale->payments()->where('reference', $reference)->first() : null;
        $hasValidSignature = $request->hasValidSignature();
        $isAuthorizedStaff = auth()->check() && (auth()->user()->can('view', $sale) || auth()->user()->isSuperAdmin());

        if (! $payment && ! $hasValidSignature && ! $isAuthorizedStaff) {
            abort(403, 'Unauthorized order confirmation access. Please access your confirmation using the link provided during checkout.');
        }

        $sale->load(['items.product', 'payments']);

        return view('payment.success', [
            'sale' => $sale,
            'reference' => $reference ?: $sale->payments()->latest()->value('reference'),
            'payment' => $payment ?: $sale->payments()->latest()->first(),
        ]);
    }

    /**
     * Customer printable receipt view.
     */
    public function receipt(Request $request, Sale $sale)
    {
        $reference = $request->query('reference');

        // Security check: Must have a matching payment reference on this sale,
        // OR a valid cryptographic signature, OR an authenticated authorized staff user.
        $refValid = $reference && $sale->payments()->where('reference', $reference)->exists();
        $signedValid = $request->hasValidSignature();
        $staffValid = auth()->check() && (auth()->user()->can('view', $sale) || auth()->user()->isSuperAdmin());

        if (! $refValid && ! $signedValid && ! $staffValid) {
            abort(403, 'Unauthorized receipt access. Please access your official receipt using the secure link provided on your payment confirmation or email.');
        }

        $sale->load(['items.product', 'payments']);

        return view('payment.receipt', [
            'sale' => $sale,
        ]);
    }

    /**
     * Handle server-to-server Paystack webhooks (charge.success, etc.).
     */
    public function webhook(Request $request, PaystackService $paystack)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $paystack->isValidWebhookSignature($signature, $payload)) {
            Log::warning('Paystack webhook signature verification failed.');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event) || empty($event['event'])) {
            return response()->json(['message' => 'No event payload'], 400);
        }

        if ($event['event'] === 'charge.success') {
            $data = $event['data'] ?? [];
            $reference = $data['reference'] ?? null;

            if ($reference) {
                $saleId = $data['metadata']['sale_id'] ?? null;
                if (! $saleId && preg_match('/ATH-PAY-(\d+)-/', $reference, $matches)) {
                    $saleId = (int) $matches[1];
                }

                if ($saleId && $sale = Sale::find($saleId)) {
                    $exists = SalePayment::where('reference', $reference)->exists();

                    if (! $exists) {
                        $amountInNaira = round(((float) ($data['amount'] ?? 0)) / 100, 2);
                        $channel = $data['channel'] ?? 'card';

                        $payment = SalePayment::create([
                            'sale_id' => $sale->id,
                            'amount' => $amountInNaira,
                            'payment_date' => now()->toDateString(),
                            'payment_method' => 'paystack',
                            'reference' => $reference,
                            'notes' => "Webhook confirmed Paystack payment ({$channel})",
                        ]);

                        $sale->status = SaleStatus::Processing;
                        $sale->paid_at = now();
                        $sale->recalculateTotals(save: true);

                        $this->sendReceiptNotification($sale, $payment);

                        Log::info("Paystack webhook successfully processed for sale #{$sale->invoice_number}");
                    }
                }
            }
        }

        return response()->json(['status' => true], 200);
    }

    /**
     * Send order confirmation and payment receipt email to customer if email is present.
     */
    protected function sendReceiptNotification(Sale $sale, SalePayment $payment): void
    {
        if (empty($sale->customer_email)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($sale->customer_email)
                ->send(new \App\Mail\OrderReceiptMail($sale, $payment));

            Log::info("Receipt email successfully queued/sent to {$sale->customer_email} for sale #{$sale->invoice_number}");
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch receipt email to {$sale->customer_email} for sale #{$sale->invoice_number}: " . $e->getMessage());
        }
    }
}

