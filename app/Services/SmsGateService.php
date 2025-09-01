<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGateService
{
    private string $baseUrl;
    private string $username;
    private string $password;

    public function __construct()
    {
        // Set these in your .env file
        $this->baseUrl = config('services.sms_gate.url'); // e.g., http://192.168.1.100:8080
        $this->username = config('services.sms_gate.user');
        $this->password = config('services.sms_gate.pass');
    }

    /**
     * Send SMS message
     */
    public function sendSms(string $phoneNumber, string $message, int $simSlot = 1): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/message", [
                    'message' => $message,
                    'phoneNumbers' => [$phoneNumber],
                    'simSlot' => $simSlot, // 1 or 2 for dual SIM
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('SMS sent successfully', ['response' => $data]);

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'SMS sent successfully'
                ];
            } else {
                Log::error('SMS sending failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                    'message' => 'Failed to send SMS'
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS Gate connection error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Connection error'
            ];
        }
    }

    /**
     * Get message status
     */
    public function getMessageStatus(string $messageId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->get("{$this->baseUrl}/v1/message/{$messageId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all messages
     */
    public function getMessages(): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->get("{$this->baseUrl}/v1/message");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check server health
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->get("{$this->baseUrl}/v1/health");

            return [
                'success' => $response->successful(),
                'data' => $response->successful() ? $response->json() : null,
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
