<?php

namespace App\Events;

class TransferCompleted
{
    public $payerId;
    public $payeeId;
    public $value;

    public function __construct($payerId, $payeeId, $value)
    {
        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
        $this->value = $value;
    }
}