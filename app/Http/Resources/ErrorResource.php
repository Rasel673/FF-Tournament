<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public static $wrap = null;

    public function __construct(string $message, int $code, ?array $errors = null)
    {
        parent::__construct(compact('message', 'code', 'errors'));
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'success' => false,
            'message' => $this->resource['message'],
            'code' => $this->resource['code'],
            'errors' => $this->resource['errors'],
        ], fn ($value) => $value !== null);
    }

    public static function send(string $message, int $code, ?array $errors = null): JsonResponse
    {
        return (new static($message, $code, $errors))->response()->setStatusCode($code);
    }
}
