<?php

namespace App\Services;

class DummyPaymentService
{
    /**
     * Process a dummy payment.
     *
     * @return array ['success' => bool, 'reference' => string|null, 'message' => string]
     */
    public function processPayment(int $bookingId, float $amount, array $paymentData): array
    {
        // Simulate payment processing delay
        sleep(1);

        // Dummy logic: Fail if card number ends in '0000'
        if (isset($paymentData['card_number']) && str_ends_with($paymentData['card_number'], '0000')) {
            return [
                'success' => false,
                'reference' => null,
                'message' => 'Card declined. Please use a different card.',
            ];
        }

        // Simulate successful payment
        $reference = 'PAY-' . strtoupper(uniqid());

        return [
            'success' => true,
            'reference' => $reference,
            'message' => 'Payment processed successfully.',
        ];
    }
}
