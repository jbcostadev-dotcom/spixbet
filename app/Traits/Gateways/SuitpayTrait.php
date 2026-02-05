<?php

namespace App\Traits\Gateways;

use App\Models\AffiliateHistory;
use App\Models\Deposit;
use App\Models\GamesKey;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\SuitPayPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\NewDepositNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Core as Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SuitpayTrait
{
    /**
     * @var $uri
     * @var $clienteId
     * @var $clienteSecret
     */
    protected static string $uri;
    protected static string $clienteId;
    protected static string $clienteSecret;

  
    private static function generateCredentials()
    {
        $setting = Gateway::first();
        if(!empty($setting)) {
            self::$uri = $setting->getAttributes()['suitpay_uri'];
            self::$clienteId = $setting->getAttributes()['suitpay_cliente_id'];
            self::$clienteSecret = $setting->getAttributes()['suitpay_cliente_secret'];
        }
    }

    /**
     * Request QRCODE - COM LOGS DETALHADOS PARA DEBUG
     * Metodo para solicitar uma QRCODE PIX
     * @dev @dracman999
     * @return array
     */
    public static function requestQrcode($request)
    {
        try {
            \Log::info('🔍 [TRAIT DEBUG] === INICIANDO requestQrcode ===');
            \Log::info('🔍 [TRAIT DEBUG] Request recebido', ['request' => $request->all()]);

            // Obtendo configurações
            $setting = \Helper::getSetting();

            // Validando os dados recebidos
            $validator = Validator::make($request->all(), [
                'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
                'cpf'    => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                \Log::warning('🔍 [TRAIT DEBUG] Validação falhou', ['errors' => $validator->errors()]);
                return response()->json($validator->errors(), 400);
            }

            // Gerar as credenciais
            self::generateCredentials();

            // Dados a serem enviados para gerar o QR Code
            $postData = [
                'client_id' => self::$clienteId,
                'client_secret' => self::$clienteSecret,
                'nome' => auth('api')->user()->name,
                'cpf' => \Helper::soNumero($request->input("cpf")),
                'valor' => (float) $request->input("amount"),
                'descricao' => 'Depósito via PIX',
                'urlnoty' => url('/api/suitpay/webhook'),
            ];

            // URL de requisição para a API Ecompag
            $url = 'https://api.ecompag.com/v2/pix/qrcode.php';
            \Log::info('🔍 [TRAIT DEBUG] Enviando requisição para API', [
                'url' => $url,
                'postData' => $postData
            ]);

            // Enviar requisição para a API
            $response = Http::asForm()->post($url, $postData);

            \Log::info('🔍 [TRAIT DEBUG] Resposta da API recebida', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Verificar se a resposta foi bem-sucedida
            if ($response->successful()) {
                $responseData = $response->json();
                \Log::info('🔍 [TRAIT DEBUG] Resposta JSON parseada', ['responseData' => $responseData]);

                // 🔍 DEBUG: Verificar se o campo 'qrcode' existe na resposta
                $transactionId = $responseData['transactionId'] ?? null;
                $externalId = $responseData['reference_code'] ?? $responseData['transactionId'] ?? null;
                $qrcodeUrl = $responseData['qrcode'] ?? null;
                
                \Log::info('🔍 [TRAIT DEBUG] Dados extraídos da resposta', [
                    'transactionId' => $transactionId,
                    'externalId' => $externalId,
                    'qrcodeUrl_existe' => !is_null($qrcodeUrl),
                    'qrcodeUrl_length' => $qrcodeUrl ? strlen($qrcodeUrl) : 0,
                    'qrcodeUrl_primeiros_50' => $qrcodeUrl ? substr($qrcodeUrl, 0, 50) : 'NULL',
                    'campos_na_resposta' => array_keys($responseData)
                ]);
                
                if (!$transactionId || !$externalId) {
                    \Log::error('🔍 [TRAIT DEBUG] transactionId ou externalId faltando!', [
                        'transactionId' => $transactionId,
                        'externalId' => $externalId
                    ]);
                    return response()->json(['error' => 'Resposta inválida da API - faltam IDs'], 500);
                }

                if (!$qrcodeUrl) {
                    \Log::error('🔍 [TRAIT DEBUG] ⚠️ QRCODE NÃO RETORNADO PELA API!', [
                        'resposta_completa' => $responseData
                    ]);
                    // Não vamos bloquear por causa disso, mas vamos logar
                }

                // Realizar a transação e o depósito dentro de uma transação DB
                \Log::info('🔍 [TRAIT DEBUG] Iniciando DB transaction');
                
                DB::transaction(function () use ($transactionId, $request, $externalId, $qrcodeUrl) {
                    \Log::info('🔍 [TRAIT DEBUG] Dentro da DB transaction', [
                        'transactionId' => $transactionId,
                        'amount' => $request->input("amount"),
                        'externalId' => $externalId,
                        'qrcodeUrl_length' => $qrcodeUrl ? strlen($qrcodeUrl) : 0
                    ]);

                    // 🔍 CHAMANDO generateTransaction COM O QRCODE
                    self::generateTransaction($transactionId, $request->input("amount"), $externalId, $qrcodeUrl);

                    // Salvar o depósito
                    self::generateDeposit($transactionId, $request->input("amount"), $externalId);
                    
                    \Log::info('🔍 [TRAIT DEBUG] DB transaction concluída com sucesso');
                });

                \Log::info('🔍 [TRAIT DEBUG] === requestQrcode FINALIZADO COM SUCESSO ===');

                return response()->json([
                    'status' => true,
                    'transactionId' => $transactionId, 
                    'qrcode' => $responseData['qrcode'] ?? null,
                    'externalId' => $externalId 
                ]);
            }

            // Log: Falha na geração do QR Code
            \Log::error('🔍 [TRAIT DEBUG] Falha na API', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return response()->json(['error' => "Ocorreu uma falha ao entrar em contato com o banco."], 500);

        } catch (\Exception $e) {
            \Log::error('🔍 [TRAIT DEBUG] Exception capturada!', [
                'message' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    /**
     * Consult Status Transaction (BACKUP - caso webhook falhe)
     */
    public static function consultStatusTransaction()
    {
        Log::info('[Ecompag] Consultando status (backup) - últimas 5 transações');

        self::generateCredentials();

        try {
            $transactions = Transaction::where('status', '!=', 1)
                ->latest()
                ->take(5)
                ->get();

            if ($transactions->isEmpty()) {
                Log::info('[Ecompag] Nenhuma transação pendente encontrada.');
                return response()->json(['message' => 'Nenhuma transação pendente'], 200);
            }

            $validTransactions = [];
            foreach ($transactions as $transaction) {
                $timeDifference = now()->diffInMinutes($transaction->updated_at);

                if ($timeDifference <= 10) {
                    $validTransactions[] = $transaction->external_id;
                }
            }

            if (empty($validTransactions)) {
                Log::info('[Ecompag] Nenhuma transação válida para consulta.');
                return response()->json(['message' => 'Nenhuma transação recente'], 200);
            }

            $responses = [];
            foreach ($validTransactions as $externalId) {
                $statusUrl = 'https://api.ecompag.com/v2/pix/status.php?transactionId=' . $externalId;

                Log::info('[Ecompag] Consultando status', ['external_id' => $externalId]);

                $response = Http::withHeaders([
                    'client_id' => self::$clienteId,
                    'client_secret' => self::$clienteSecret
                ])->get($statusUrl);

                if (!$response->successful()) {
                    Log::error('[Ecompag] Falha na consulta', ['external_id' => $externalId]);
                    $responses[$externalId] = ['status' => 'pendente'];
                    continue;
                }

                $statusData = $response->json();

                if (isset($statusData['status'])) {
                    $transactionStatus = $statusData['status'];
                    
                    if ($transactionStatus === 'PAID') {
                        Log::notice('[Ecompag] Pagamento confirmado via consulta', ['external_id' => $externalId]);
                        self::finalizePayment($externalId);
                    }

                    $responses[$externalId] = ['status' => $transactionStatus];
                }
            }

            return response()->json($responses);
        } catch (\Exception $e) {
            Log::critical('[Ecompag] Erro crítico', [
                'erro' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    /**
     * Finalizar pagamento via WEBHOOK
     */
    public static function finalizePaymentViaWebhook(string $externalId): bool
    {
        Log::info("[Ecompag Webhook] Finalizando pagamento", ['external_id' => $externalId]);
        return self::finalizePayment($externalId);
    }

    /**
     * Finalizar Pagamento
     */
    public static function finalizePayment($externalId) : bool
    {
        \Log::info("[Ecompag] Iniciando finalização do pagamento com external_id: $externalId");

        $transaction = Transaction::where('external_id', $externalId)->where('status', 0)->first();
        if (!$transaction) {
            \Log::error("[Ecompag] Transação não encontrada para o external_id: $externalId");
            return false;
        }
        \Log::info("[Ecompag] Transação encontrada", ['id' => $transaction->id]);

        $setting = \Helper::getSetting();
        $user = User::find($transaction->user_id);
        \Log::info("[Ecompag] Usuário encontrado", ['id' => $user->id]);

        $wallet = Wallet::where('user_id', $transaction->user_id)->first();
        if (!$wallet) {
            \Log::error("[Ecompag] Carteira não encontrada");
            return false;
        }
        \Log::info("[Ecompag] Carteira encontrada");

        $checkTransactions = Transaction::where('user_id', $transaction->user_id)
            ->where('status', 1)
            ->count();
        \Log::info("[Ecompag] Transações anteriores: $checkTransactions");

        if ($checkTransactions == 0) {
            $bonus = Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
            \Log::info("[Ecompag] Pagando bônus inicial: $bonus");
            $wallet->increment('balance_bonus', $bonus);
            $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
        }

        $wallet->update(['balance_deposit_rollover' => $transaction->price * intval($setting->rollover_deposit)]);
        \Log::info("[Ecompag] Aplicando rollover ao depósito");

        Helper::payBonusVip($wallet, $transaction->price);
        \Log::info("[Ecompag] Pagando bônus VIP");

        if ($wallet->increment('balance', $transaction->price)) {
            \Log::info("[Ecompag] Saldo do usuário atualizado");

            if ($transaction->update(['status' => 1])) {
                \Log::info("[Ecompag] Status da transação atualizado para 'pago'");

                $deposit = Deposit::where('external_id', $externalId)->where('status', 0)->first();
                if (!empty($deposit)) {
                    \Log::info("[Ecompag] Depósito encontrado", ['id' => $deposit->id]);

                    $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                        ->where('commission_type', 'cpa')
                        ->where('status', 0)
                        ->first();
                    if (!empty($affHistoryCPA)) {
                        \Log::info("[Ecompag] Verificando histórico de CPA");

                        $sponsorCpa = User::find($user->inviter);
                        if (!empty($sponsorCpa)) {
                            \Log::info("[Ecompag] Sponsor encontrado para CPA", ['id' => $sponsorCpa->id]);
                            if ($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();
                                if (!empty($walletCpa)) {
                                    $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa);
                                    $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]);
                                    \Log::info("[Ecompag] CPA pago ao sponsor", ['sponsor_id' => $sponsorCpa->id]);
                                }
                            } else {
                                $affHistoryCPA->update(['deposited_amount' => $transaction->price]);
                                \Log::info("[Ecompag] Valor depositado atualizado no CPA");
                            }
                        }
                    }

                    if ($deposit->update(['status' => 1])) {
                        \Log::info("[Ecompag] Depósito marcado como pago ✅");

                        $admins = User::where('role_id', 0)->get();
                        foreach ($admins as $admin) {
                            $admin->notify(new NewDepositNotification($user->name, $transaction->price));
                            \Log::info("[Ecompag] Notificação enviada ao admin", ['admin_id' => $admin->id]);
                        }
                    }
                }
            }
        } else {
            \Log::error("[Ecompag] Erro ao atualizar o saldo do usuário");
            return false;
        }

        return true;
    }

    /**
     * Gerar Depósito
     */
    private static function generateDeposit($idTransaction, $amount, $externalId)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id'=> $idTransaction,
            'user_id'   => $userId,
            'amount'    => $amount,
            'type'      => 'pix',
            'currency'  => $wallet->currency,
            'symbol'    => $wallet->symbol,
            'status'    => 0,
            'external_id' => $externalId,
        ]);
    }

    /**
     * 🔍 Gerar Transação - COM LOGS DETALHADOS
     */
    private static function generateTransaction($idTransaction, $amount, $externalId, $qrcodeUrl = null)
    {
        \Log::info('🔍 [TRAIT DEBUG] === generateTransaction CHAMADO ===', [
            'idTransaction' => $idTransaction,
            'amount' => $amount,
            'externalId' => $externalId,
            'qrcodeUrl_recebido' => !is_null($qrcodeUrl),
            'qrcodeUrl_length' => $qrcodeUrl ? strlen($qrcodeUrl) : 0,
            'qrcodeUrl_primeiros_100' => $qrcodeUrl ? substr($qrcodeUrl, 0, 100) : 'NULL'
        ]);

        $setting = \Helper::getSetting();
        $userId = auth('api')->user()->id;

        $dataToInsert = [
            'payment_id' => $idTransaction,
            'user_id' => $userId,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0,
            'external_id' => $externalId,
            'qrcode_url' => $qrcodeUrl, // 🔍 SALVANDO O QRCODE
        ];

        \Log::info('🔍 [TRAIT DEBUG] Dados que serão inseridos no banco', [
            'dataToInsert' => $dataToInsert,
            'qrcode_url_no_array' => isset($dataToInsert['qrcode_url']),
            'qrcode_url_valor' => $dataToInsert['qrcode_url'] ?? 'UNDEFINED'
        ]);

        try {
            $transaction = Transaction::create($dataToInsert);
            
            \Log::info('🔍 [TRAIT DEBUG] Transaction criada no banco!', [
                'transaction_id' => $transaction->id,
                'qrcode_url_salvo' => $transaction->qrcode_url,
                'qrcode_url_length_salvo' => strlen($transaction->qrcode_url ?? '')
            ]);

            // 🔍 VERIFICAÇÃO: Buscar novamente para confirmar
            $verificacao = Transaction::find($transaction->id);
            \Log::info('🔍 [TRAIT DEBUG] Verificação após salvar', [
                'qrcode_url_no_banco' => $verificacao->qrcode_url,
                'qrcode_url_length_no_banco' => strlen($verificacao->qrcode_url ?? ''),
                'campo_eh_null' => is_null($verificacao->qrcode_url)
            ]);

        } catch (\Exception $e) {
            \Log::error('🔍 [TRAIT DEBUG] ERRO ao criar Transaction!', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Realizar saque via PIX (Ecompag)
     */
    public static function pixCashOut(array $array): bool
    {
        self::generateCredentials();

        $response = Http::asForm()->post('https://api.ecompag.com/v2/pix/payment.php', [
            'client_id' => self::$clienteId,
            'client_secret' => self::$clienteSecret,
            'nome' => $array['name'] ?? 'Beneficiário',
            'cpf' => \Helper::soNumero($array['cpf'] ?? ''),
            'valor' => (float) $array['amount'],
            'chave_pix' => $array['pix_key'],
            'descricao' => 'Saque via PIX',
            'urlnoty' => url('/api/suitpay/webhook'),
        ]);

        if($response->successful()) {
            $responseData = $response->json();

            if(isset($responseData['status']) && in_array($responseData['status'], ['PAID', 'PENDING'])) {
                $suitPayPayment = SuitPayPayment::lockForUpdate()->find($array['suitpayment_id']);
                if(!empty($suitPayPayment)) {
                    $paymentId = $responseData['transactionId'] ?? $responseData['external_id'] ?? null;
                    if($suitPayPayment->update(['status' => 1, 'payment_id' => $paymentId])) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        return false;
    }
}