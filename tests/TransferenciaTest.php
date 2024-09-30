<?php

namespace Tests\Feature;

use Tests\TestCase;
// use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferenciaTest extends TestCase
{
    // use RefreshDatabase;

    public function test_transferencia_sucesso()
    {
        $this->json('post', '/transfer', [
            'value' => 100.0,
            'payer' => 4,
            'payee' => 5
        ])->seeJson([
            'message' => 'Transferência realizada com sucesso',
            'code' => 'TRANSFER_SUCCESS'
        ]);
    }
}
