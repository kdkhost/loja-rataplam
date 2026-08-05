<?php
namespace App\Services\MercadoPago;

use App\Models\MercadoPagoAction;

class MercadoPagoIdempotencyAcquisitionResult
{
    public const REASON_ACQUIRED_NEW = 'acquired_new';
    public const REASON_ACQUIRED_STALE = 'acquired_stale';
    public const REASON_EXISTING_IN_PROGRESS = 'existing_in_progress';
    public const REASON_EXISTING_SUCCESS = 'existing_success';
    public const REASON_EXISTING_FAILED = 'existing_failed';
    public const REASON_EXISTING_UNKNOWN = 'existing_unknown';

    public function __construct(
        public readonly MercadoPagoAction $action,
        public readonly bool $acquiredForExecution,
        public readonly string $reason,
        public readonly ?string $executionOwner = null
    ) {}

    public static function acquiredNew(MercadoPagoAction $action, string $executionOwner): self
    {
        return new self(
            $action,
            true,
            self::REASON_ACQUIRED_NEW,
            $executionOwner
        );
    }

    public static function acquiredStale(MercadoPagoAction $action, string $executionOwner): self
    {
        return new self(
            $action,
            true,
            self::REASON_ACQUIRED_STALE,
            $executionOwner
        );
    }

    public static function existingInProgress(MercadoPagoAction $action): self
    {
        return new self(
            $action,
            false,
            self::REASON_EXISTING_IN_PROGRESS
        );
    }

    public static function existingSuccess(MercadoPagoAction $action): self
    {
        return new self(
            $action,
            false,
            self::REASON_EXISTING_SUCCESS
        );
    }

    public static function existingFailed(MercadoPagoAction $action): self
    {
        return new self(
            $action,
            false,
            self::REASON_EXISTING_FAILED
        );
    }

    public static function existingUnknown(MercadoPagoAction $action): self
    {
        return new self(
            $action,
            false,
            self::REASON_EXISTING_UNKNOWN
        );
    }

    public function canExecuteRemoteCall(): bool
    {
        return $this->acquiredForExecution;
    }

    public function shouldRetryWithSameKey(): bool
    {
        return in_array($this->reason, [
            self::REASON_EXISTING_FAILED,
            self::REASON_EXISTING_UNKNOWN,
        ]);
    }

    public function requiresReconciliation(): bool
    {
        return $this->reason === self::REASON_EXISTING_UNKNOWN;
    }
}
