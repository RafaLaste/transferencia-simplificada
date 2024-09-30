<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\TransferService;
use App\Events\TransferStarted;
use App\Events\TransferCompleted;

use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{   
    protected $transferenciaService;

    public function __construct(TransferService $transferenciaService)
    {
        $this->transferenciaService = $transferenciaService;
    }

    public function transferencia(Request $request)
    {   
        $this->validate($request, [
            'value' => 'required|numeric|min:0.01',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ],
        [
            'value.required' => 'O valor da transferência é obrigatório.',
            'value.numeric'  => 'O valor deve ser numérico.',
            'value.min'      => 'O valor mínimo para transferência é R$ 0.01.',
            'payer.required' => 'O pagador é obrigatório.',
            'payer.exists'   => 'O pagador não foi encontrado.',
            'payee.required' => 'O recebedor é obrigatório.',
            'payee.exists'   => 'O recebedor não foi encontrado.',
        ]);

        Log::info('Iniciando transferência', [
            'payer_id' => $request->payer,
            'payee_id' => $request->payee,
            'value' => $request->value
        ]);

        event(new TransferStarted($request->payer, $request->payee, $request->value));

        try {
            $this->transferenciaService->runTransfer($request->payer, $request->payee, $request->value);

            event(new TransferCompleted($request->payer, $request->payee, $request->value));

            return response()->json([
                'message' => 'Transferência realizada com sucesso.',
                'code' => 'TRANSFER_SUCCESS'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar a transferência', [
                'error' => $e->getMessage(),
                'payer_id' => $request->payer,
                'payee_id' => $request->payee
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'TRANSFER_PROCESSING_ERROR'
            ], 500);
        }
    }
}