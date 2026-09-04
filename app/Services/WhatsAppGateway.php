<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppGateway
{
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        Log::info('Mock WhatsApp OTP dispatched', ['phone_number' => $phoneNumber, 'otp' => $otp]);

        return true;
    }
}
