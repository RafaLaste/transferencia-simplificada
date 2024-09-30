<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transfer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpClient;
use Psr\Log\LoggerInterface;
use Carbon\Carbon;
use Exception;

class TransferService
{
    protected $database;
    protected $cache;
    protected $http;
    protected $logger;
    protected $transfer;

    public function __construct(
        DatabaseManager $database,
        CacheRepository $cache,
        HttpClient $http,
        LoggerInterface $logger,
        transfer $transfer
    ) {
        $this->database = $database;
        $this->cache = $cache;
        $this->http = $http;
        $this->logger = $logger;
        $this->transfer = $transfer;
    }

    public function runTransfer($payerId, $payeeId, $value)
    {
        $this->database->beginTransaction();

        try {
            $payer = $this->cache->remember("payer_{$payerId}", 60, function () use ($payerId) {
                // return User::find($payerId);

                return User::query()
                    ->where([
                        'deleted_at' => NULL,
                        'id' => $payerId
                    ])
                    ->first();
            });

            if (!$payer) {
                throw new Exception('Pagador não encontrado.');
            }

            $payee = $this->cache->remember("payee_{$payeeId}", 60, function () use ($payeeId) {
                // return User::find($payeeId);

                return User::query()
                    ->where([
                        'deleted_at' => NULL,
                        'id' => $payeeId
                    ])
                    ->first();
            });

            if (!$payee) {
                throw new Exception('Beneficiário não encontrado.');
            }

            if ($payer->tipo == 'lojista') {
                throw new Exception('Usuários lojistas não podem enviar valores.');
            }

            if ($payer->saldo < $value) {
                throw new Exception('Saldo insuficiente.');
            }

            $this->authorizeTransfer($payer, $payee, $value);

            $payer->decrement('saldo', $value);
            $payee->increment('saldo', $value);

            $this->transfer->create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'value' => $value,
                'status' => 'aprovado',
            ]);

            $this->database->commit();

            // $this->cache->forget("payer_{$payer->id}");
            // $this->cache->forget("payee_{$payee->id}");

            $this->sendNotification($payee->email, $value, $payer->nome_completo, $payee->nome_completo);

        } catch (Exception $e) {
            $this->database->rollBack();

            $this->logger->error('Erro ao processar a transferência', [
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
                'erro' => $e->getMessage(),
            ]);

            throw new Exception($e->getMessage());
        }
    }

    protected function authorizeTransfer($payer, $payee, $value)
    {
        $authorizeResponse = $this->http->get('https://util.devi.tools/api/v2/authorize', [
            'query' => [
                'value' => $value,
                'payer' => $payer->id,
                'payee' => $payee->id,
            ]
        ]);

        if ($authorizeResponse->failed()) {
            throw new Exception('Autorização negada pelo serviço externo.');
        }
    }
    
    protected function sendNotification($email, $value, $payerName, $payeeName)
    {
        $message = "Você recebeu uma transferência de {$payerName} no valor de R$ " . number_format($value, 2, ',', '.') . ' em ' . Carbon::now()->format('d/m/Y H:i');

        $notificationResponse = $this->http->post('https://util.devi.tools/api/v1/notify', [
            'query' => [
                'to' => $email,
                'message' => $message,
            ]
        ]);

        if ($notificationResponse->failed()) {
            $this->logger->error('Falha ao enviar notificação para o beneficiário', [
                'payer' => $payerName,
                'payee' => $payeeName,
                'value' => $value,
                'response' => $notificationResponse->body()
            ]);
        }
    }
}
