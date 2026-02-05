<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuitPayController extends Controller
{
    /**
     * Processar saque via modal (recebe parâmetros na URL)
     * URL: /suitpay/withdrawal/{id}/{action}
     */
    public function withdrawalFromModal($id, $action)
    {
        Log::info('=== ECOMPAG: Iniciando processamento de saque ===', [
            'withdrawal_id' => $id,
            'action' => $action,
            'user_id' => auth()->id(),
            'url' => request()->fullUrl()
        ]);

        try {
            $withdrawal = Withdrawal::with('user')->find($id);
            
            if (!$withdrawal) {
                Log::error('Ecompag: Saque não encontrado', ['id' => $id]);
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Saque não encontrado');
            }

            if ($withdrawal->status == 1) {
                Log::warning('Ecompag: Saque já processado', ['withdrawal_id' => $withdrawal->id]);
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Este saque já foi processado');
            }

            // Buscar credenciais do gateway
            $gateway = Gateway::first();
            
            if (!$gateway || !$gateway->suitpay_uri || !$gateway->suitpay_cliente_id || !$gateway->suitpay_cliente_secret) {
                Log::error('Ecompag: Gateway não configurado');
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Gateway não configurado corretamente');
            }

            // Validar chave PIX do destinatário (usuário)
            if (!$withdrawal->pix_key) {
                Log::error('Ecompag: Chave PIX não informada', ['withdrawal_id' => $withdrawal->id]);
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Chave PIX do usuário não informada');
            }

            // ============================================
            // OBTER DADOS DO USUÁRIO DO BANCO DE DADOS
            // ============================================
            $userName = $withdrawal->user->name ?? 'Usuário';
            
            // Tentar usar o CPF da tabela withdrawals, senão tentar a chave PIX
            $userCpf = $withdrawal->cpf ?? null;
            
            // Se não tiver CPF e a chave PIX for CPF/documento, usar ela
            if (empty($userCpf) && $withdrawal->pix_type === 'document') {
                $userCpf = $withdrawal->pix_key;
            }
            
            // Limpar CPF (remover pontos, traços)
            $userCpf = preg_replace('/[^0-9]/', '', $userCpf);
            
            if (empty($userCpf) || strlen($userCpf) != 11) {
                Log::error('Ecompag: CPF inválido ou não informado', [
                    'withdrawal_id' => $withdrawal->id,
                    'cpf' => $userCpf,
                    'pix_key' => $withdrawal->pix_key,
                    'pix_type' => $withdrawal->pix_type
                ]);
                
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'CPF do usuário não encontrado ou inválido');
            }

            Log::info('Ecompag: Dados do usuário obtidos', [
                'user_id' => $withdrawal->user_id,
                'name' => $userName,
                'cpf' => substr($userCpf, 0, 3) . '***' . substr($userCpf, -2) // Log parcial por segurança
            ]);
            // ============================================

            // API Ecompag - Endpoint de transferência
            $apiUrl = 'https://api.ecompag.com/v2/pix/payment.php';
            
            // Dados para a API
            $postData = [
                'client_id' => $gateway->suitpay_cliente_id,
                'client_secret' => $gateway->suitpay_cliente_secret,
                'nome' => $userName,
                'cpf' => $userCpf,
                'valor' => floatval($withdrawal->amount),
                'chave_pix' => $withdrawal->pix_key,
                'descricao' => 'Saque via PIX',
                'urlnoty' => url('/api/suitpay/webhook')
            ];

            Log::info('Ecompag: Enviando requisição de transferência', [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'pix_key_destino' => $withdrawal->pix_key,
                'api_url' => $apiUrl
            ]);

            // Fazer requisição para a API
            $response = Http::asForm()->timeout(30)->post($apiUrl, $postData);
            $statusCode = $response->status();
            $responseData = $response->json();

            Log::info('Ecompag: Resposta da API recebida', [
                'status_code' => $statusCode,
                'response_data' => $responseData
            ]);

            // Verificar resposta da Ecompag
            if ($statusCode === 200 && isset($responseData['statusCode']) && $responseData['statusCode'] == 200) {
                
                $transactionId = $responseData['transactionId'] ?? null;
                $externalId = $responseData['external_id'] ?? $transactionId; // Usar transactionId se external_id não existir
                $status = $responseData['status'] ?? 'PENDING';
                $message = $responseData['message'] ?? 'Transferência processada';
                
                $withdrawal->update([
                    'status' => ($status === 'PAID') ? 1 : 0,
                    'bank_info' => json_encode([
                        'transaction_id' => $transactionId,
                        'external_id' => $externalId,
                        'status' => $status,
                        'message' => $message,
                        'pix_key' => $withdrawal->pix_key,
                        'amount' => $withdrawal->amount,
                        'processed_at' => now()->format('Y-m-d H:i:s'),
                        'processed_by' => auth()->user()->name ?? 'Sistema'
                    ])
                ]);

                Log::info('Ecompag: Saque PROCESSADO ✅', [
                    'withdrawal_id' => $withdrawal->id,
                    'transaction_id' => $transactionId,
                    'external_id' => $externalId,
                    'status' => $status,
                    'amount' => $withdrawal->amount,
                    'pix_key_destino' => $withdrawal->pix_key
                ]);

                return redirect()
                    ->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('success', 'Saque processado! Status: ' . $status . ' | ID: ' . $transactionId);
            }

            // Tratamento de erros
            $errorMessage = $responseData['message'] ?? 'Erro ao processar transferência';
            
            Log::error('Ecompag: ERRO ao processar transferência', [
                'withdrawal_id' => $withdrawal->id,
                'error_message' => $errorMessage,
                'full_response' => $responseData,
                'status_code' => $statusCode
            ]);

            return redirect()
                ->route('filament.admin.resources.todos-saques-historico.index')
                ->with('error', 'Erro Ecompag: ' . $errorMessage);

        } catch (\Exception $e) {
            Log::error('Ecompag: Exception capturada', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $id ?? null
            ]);

            return redirect()
                ->route('filament.admin.resources.todos-saques-historico.index')
                ->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Processar saque via query params (backup)
     */
    public function withdrawal(Request $request)
    {
        $withdrawalId = $request->input('id');
        
        Log::info('Ecompag: Redirecionando para método principal', [
            'id' => $withdrawalId
        ]);
        
        return $this->withdrawalFromModal($withdrawalId, 'user');
    }

    /**
     * Cancelar saque via modal
     */
    public function cancelWithdrawalFromModal($id, $action)
    {
        Log::info('=== ECOMPAG: Iniciando cancelamento de saque ===', [
            'withdrawal_id' => $id,
            'action' => $action,
            'user_id' => auth()->id()
        ]);

        try {
            $withdrawal = Withdrawal::with('user')->find($id);
            
            if (!$withdrawal) {
                Log::error('Ecompag: Saque não encontrado para cancelamento', ['id' => $id]);
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Saque não encontrado');
            }

            if ($withdrawal->status == 1) {
                Log::warning('Ecompag: Tentativa de cancelar saque já processado', [
                    'withdrawal_id' => $withdrawal->id
                ]);
                return redirect()->route('filament.admin.resources.todos-saques-historico.index')
                    ->with('error', 'Saque já foi pago, não pode ser cancelado');
            }

            // Devolver saldo ao usuário
            $withdrawal->user->increment('balance', $withdrawal->amount);

            $withdrawal->update([
                'status' => 2,
                'bank_info' => json_encode([
                    'cancelled_at' => now()->format('Y-m-d H:i:s'),
                    'cancelled_by' => auth()->user()->name ?? 'Sistema',
                    'amount_returned' => $withdrawal->amount
                ])
            ]);

            Log::info('Ecompag: Saque CANCELADO ✅', [
                'withdrawal_id' => $withdrawal->id,
                'amount_returned' => $withdrawal->amount,
                'user_id' => $withdrawal->user_id
            ]);

            return redirect()
                ->route('filament.admin.resources.todos-saques-historico.index')
                ->with('success', 'Saque cancelado! R$ ' . number_format($withdrawal->amount, 2, ',', '.') . ' devolvido ao usuário');

        } catch (\Exception $e) {
            Log::error('Ecompag: Erro ao cancelar saque', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $id ?? null
            ]);

            return redirect()
                ->route('filament.admin.resources.todos-saques-historico.index')
                ->with('error', 'Erro ao cancelar: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar saque via query params (backup)
     */
    public function cancelWithdrawal(Request $request)
    {
        $withdrawalId = $request->input('id');
        
        Log::info('Ecompag: Redirecionando para método de cancelamento principal', [
            'id' => $withdrawalId
        ]);
        
        return $this->cancelWithdrawalFromModal($withdrawalId, 'user');
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * MÉTODO ANTIGO: Verificar status por token (manter compatibilidade)
     * ═══════════════════════════════════════════════════════════════
     */
    public function checkTransactionStatusByToken(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            Log::info('Verificando status da transação por token', [
                'has_token' => !empty($token),
                'user_id' => auth()->id()
            ]);
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token não fornecido'
                ], 401);
            }
            
            // Buscar transação pelo user logado
            $transaction = Transaction::where('user_id', auth()->id())
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma transação encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
                'amount' => $transaction->price
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status por token', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status'
            ], 500);
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * NOVO MÉTODO: Verificar Pagamento PIX - COM LOGS DETALHADOS
     * Chamado pelo botão "Já Paguei" no frontend
     * ═══════════════════════════════════════════════════════════════
     */
    public function checkPayment(Request $request)
    {
        try {
            $qrcode = $request->input('qrcode');
            
            // 🔍 LOG DETALHADO 1: O que está chegando?
            Log::info('🔍 [DEBUG] Verificando pagamento PIX', [
                'qrcode_recebido' => $qrcode,
                'qrcode_length' => strlen($qrcode),
                'user_id' => auth()->id(),
                'primeiros_50_chars' => substr($qrcode, 0, 50),
                'ultimos_50_chars' => substr($qrcode, -50)
            ]);
            
            if (empty($qrcode)) {
                return response()->json([
                    'success' => false,
                    'paid' => false,
                    'message' => 'Código PIX não informado'
                ], 400);
            }
            
            // 🔍 LOG DETALHADO 2: Vamos ver o que tem no banco ANTES da busca
            $todasTransacoes = Transaction::where('user_id', auth()->id())
                ->orderBy('id', 'desc')
                ->take(5)
                ->get(['id', 'qrcode_url', 'status', 'external_id', 'created_at']);
            
            Log::info('🔍 [DEBUG] Transações do usuário no banco (últimas 5)', [
                'user_id' => auth()->id(),
                'total_transacoes' => $todasTransacoes->count(),
                'transacoes' => $todasTransacoes->map(function($t) {
                    return [
                        'id' => $t->id,
                        'status' => $t->status,
                        'external_id' => $t->external_id,
                        'qrcode_url_existe' => !empty($t->qrcode_url),
                        'qrcode_url_length' => strlen($t->qrcode_url ?? ''),
                        'qrcode_primeiros_50' => substr($t->qrcode_url ?? '', 0, 50),
                        'created_at' => $t->created_at
                    ];
                })
            ]);
            
            // 🔍 BUSCA COM LOG DETALHADO
            $transaction = Transaction::where('qrcode_url', $qrcode)
                ->where('user_id', auth()->id())
                ->orderBy('id', 'desc')
                ->first();
            
            Log::info('🔍 [DEBUG] Resultado da busca', [
                'encontrou_transacao' => !is_null($transaction),
                'transaction_id' => $transaction->id ?? null,
                'qrcode_no_banco_match' => $transaction ? ($transaction->qrcode_url === $qrcode) : false
            ]);
            
            if (!$transaction) {
                // 🔍 Vamos tentar buscar por LIKE para ver se é problema de encoding
                $transactionLike = Transaction::where('qrcode_url', 'LIKE', '%' . substr($qrcode, 10, 50) . '%')
                    ->where('user_id', auth()->id())
                    ->first();
                
                Log::warning('🔍 [DEBUG] Transação não encontrada - tentando LIKE', [
                    'encontrou_com_like' => !is_null($transactionLike),
                    'qrcode_enviado_length' => strlen($qrcode),
                    'user_id' => auth()->id(),
                    'substring_buscada' => substr($qrcode, 10, 50)
                ]);
                
                return response()->json([
                    'success' => false,
                    'paid' => false,
                    'message' => 'Transação não encontrada',
                    'debug' => [
                        'qrcode_length' => strlen($qrcode),
                        'total_user_transactions' => $todasTransacoes->count()
                    ]
                ], 404);
            }
            
            Log::info('✅ Transação encontrada', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
                'amount' => $transaction->price,
                'external_id' => $transaction->external_id
            ]);
            
            // Verificar status da transação
            if ($transaction->status == 1) {
                // JÁ ESTÁ PAGO!
                Log::info('✅ Pagamento JÁ CONFIRMADO!', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'amount' => $transaction->price
                ]);
                
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'message' => 'Pagamento confirmado!',
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->price
                ], 200);
            }
            
            // Se ainda não está pago (status 0), vamos consultar a API Ecompag
            $gateway = Gateway::first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'paid' => false,
                    'message' => 'Gateway não configurado'
                ], 500);
            }
            
            // Consultar status na API Ecompag
            $external_id = $transaction->external_id ?? $transaction->payment_id;
            
            if ($external_id) {
                Log::info('Consultando API Ecompag', [
                    'external_id' => $external_id
                ]);
                
                try {
                    $apiUrl = 'https://api.ecompag.com/v2/pix/consulta.php';
                    
                    $response = Http::asForm()->timeout(10)->post($apiUrl, [
                        'client_id' => $gateway->suitpay_cliente_id,
                        'client_secret' => $gateway->suitpay_cliente_secret,
                        'external_id' => $external_id
                    ]);
                    
                    $responseData = $response->json();
                    
                    Log::info('Resposta API Ecompag', [
                        'response' => $responseData
                    ]);
                    
                    // Verificar se foi pago
                    if (isset($responseData['status']) && $responseData['status'] === 'PAID') {
                        // PAGO! Atualizar transação
                        Log::info('✅ PAGAMENTO CONFIRMADO pela API!', [
                            'transaction_id' => $transaction->id,
                            'external_id' => $external_id
                        ]);
                        
                        // Chamar a trait para finalizar o pagamento
                        \App\Traits\Gateways\SuitpayTrait::finalizePaymentViaWebhook($external_id);
                        
                        return response()->json([
                            'success' => true,
                            'paid' => true,
                            'message' => 'Pagamento confirmado!',
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->price
                        ], 200);
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Erro ao consultar API Ecompag', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Ainda não foi pago
            return response()->json([
                'success' => true,
                'paid' => false,
                'message' => 'Pagamento ainda não identificado',
                'transaction_id' => $transaction->id
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar pagamento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'paid' => false,
                'message' => 'Erro ao verificar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook Ecompag - Processa depósitos e saques
     */
    public function webhook(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('Ecompag Webhook recebido', ['data' => $data]);

            if (!$data) {
                return response()->json(['status' => 'error'], 400);
            }

            // Verificar se os dados estão dentro de requestBody
            if (isset($data['requestBody'])) {
                $data = $data['requestBody'];
                Log::info('Ecompag Webhook: Extraído de requestBody', ['data' => $data]);
            }

            $transactionType = $data['transactionType'] ?? null;

            // ============================================
            // WEBHOOK DE DEPÓSITO (Recebimento PIX)
            // ============================================
            if ($transactionType === 'RECEIVEPIX') {
                $transactionId = $data['transactionId'] ?? null;
                $status = $data['status'] ?? null;
                $amount = $data['amount'] ?? null;

                Log::info('Ecompag Webhook: DEPÓSITO detectado', [
                    'transactionId' => $transactionId,
                    'status' => $status,
                    'amount' => $amount
                ]);

                if ($status === 'PAID' && $transactionId) {
                    // Buscar a transação pelo transactionId (que foi salvo como payment_id ou external_id)
                    $transaction = Transaction::where(function($query) use ($transactionId) {
                        $query->where('payment_id', $transactionId)
                              ->orWhere('external_id', $transactionId);
                    })->where('status', 0)->first();

                    if ($transaction) {
                        Log::info('Ecompag Webhook: Transação encontrada', [
                            'transaction_id' => $transaction->id,
                            'external_id' => $transaction->external_id
                        ]);

                        // Chamar trait para finalizar o pagamento usando o external_id salvo no banco
                        $result = \App\Traits\Gateways\SuitpayTrait::finalizePaymentViaWebhook($transaction->external_id);
                        
                        if ($result) {
                            Log::info('Ecompag Webhook: Depósito confirmado ✅', [
                                'transaction_id' => $transactionId,
                                'external_id' => $transaction->external_id
                            ]);
                            
                            return response()->json(['status' => 'success', 'message' => 'Depósito processado'], 200);
                        } else {
                            Log::error('Ecompag Webhook: Falha ao processar depósito', [
                                'transaction_id' => $transactionId
                            ]);
                        }
                    } else {
                        Log::warning('Ecompag Webhook: Transação não encontrada no banco', [
                            'transactionId' => $transactionId
                        ]);
                    }
                }
            }

            // ============================================
            // WEBHOOK DE SAQUE (Transferência PIX)
            // ============================================
            if ($transactionType === 'PAYMENT') {
                $transactionId = $data['transactionId'] ?? null;
                $statusId = $data['statusCode']['statusId'] ?? null;

                Log::info('Ecompag Webhook: SAQUE detectado', [
                    'transaction_id' => $transactionId,
                    'status_id' => $statusId
                ]);

                if ($statusId == 1 && $transactionId) {
                    // Buscar pelo transactionId no bank_info
                    $withdrawals = Withdrawal::whereRaw(
                        "JSON_EXTRACT(bank_info, '$.transaction_id') = ?", 
                        [$transactionId]
                    )->get();

                    if ($withdrawals->isEmpty()) {
                        Log::warning('Ecompag Webhook: Nenhum saque encontrado', [
                            'transaction_id' => $transactionId
                        ]);
                    }

                    foreach ($withdrawals as $withdrawal) {
                        $bankInfo = json_decode($withdrawal->bank_info, true) ?? [];
                        $bankInfo['webhook_confirmed'] = now()->format('Y-m-d H:i:s');
                        $bankInfo['webhook_transaction_id'] = $transactionId;
                        $bankInfo['webhook_status_id'] = $statusId;

                        $withdrawal->update([
                            'status' => 1,
                            'bank_info' => json_encode($bankInfo)
                        ]);

                        Log::info('Ecompag Webhook: Saque confirmado ✅', [
                            'withdrawal_id' => $withdrawal->id,
                            'transaction_id' => $transactionId
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Ecompag Webhook: Erro ao processar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}