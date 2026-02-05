<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Resources\WithdrawalResource\Pages;
use App\Filament\Resources\WithdrawalResource\RelationManagers;
use App\Models\User;
use App\Models\Withdrawal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use App\Models\AproveWithdrawal;
use App\Models\AproveSaveSetting;
use Illuminate\Support\Facades\Hash;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Saques';

    protected static ?string $modelLabel = 'Saques';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $slug = 'todos-saques-historico';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['type', 'bank_info', 'user.name', 'user.last_name', 'user.cpf', 'user.phone', 'user.email'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 0)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 0)->count() > 5 ? 'success' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cadastro de Saques')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Usuários')
                        ->placeholder('Selecione um usuário')
                        ->relationship(name: 'user', titleAttribute: 'name')
                        ->options(
                            fn($get) => User::query()
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->label('Valor')
                        ->required()
                        ->default(0.00),
                    Forms\Components\TextInput::make('type')
                        ->label('Tipo')
                        ->required()
                        ->maxLength(191),
                    Forms\Components\FileUpload::make('proof')
                        ->label('Comprovante')
                        ->placeholder('Carregue a imagem do comprovante')
                        ->image()
                        ->columnSpanFull()
                        ->required(),
                    Forms\Components\Toggle::make('status')
                        ->required(),
                    // Seção separada para a senha de aprovação
                    Forms\Components\Section::make('Senha de confirmação de Alterações')
                        ->description('Digite sua senha de aprovação para confirmar as mudanças. OBS: SE FOR CRIAR UM DEPOSITO PODE DIGITAR QUALQUER COISA! USE APENAS PARA EXCLUSAO E EDICAO!')
                        ->schema([
                            Forms\Components\TextInput::make('approval_password_save')
                                ->label('Senha de Aprovação')
                                ->password()
                                ->required()
                                ->helperText('Digite a senha para salvar as alterações.')
                                ->maxLength(191),
                        ])->columns(2),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable(['users.name', 'users.last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn(Withdrawal $record): string => $record->symbol . ' ' . $record->amount)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pix_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn(string $state): string => \Helper::formatPixType($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pix_key')
                    ->label('Chave Pix'),
                
                // Nova coluna de status visual melhorada
                Tables\Columns\BadgeColumn::make('status_display')
                    ->label('Status')
                    ->getStateUsing(function (Withdrawal $record) {
                        if ($record->status == 1) {
                            return 'Pago';
                        }
                        if ($record->status == 2) {
                            return 'Cancelado';
                        }
                        
                        // Verificar se está processando (tem transaction_id no bank_info)
                        $bankInfo = json_decode($record->bank_info, true);
                        if (!empty($bankInfo['transaction_id'])) {
                            return 'Processando';
                        }
                        
                        return 'Pendente';
                    })
                    ->colors([
                        'success' => 'Pago',
                        'danger' => 'Cancelado',
                        'warning' => 'Processando',
                        'gray' => 'Pendente',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Pago',
                        'heroicon-o-x-circle' => 'Cancelado',
                        'heroicon-o-clock' => 'Processando',
                        'heroicon-o-exclamation-circle' => 'Pendente',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // ========================================
                // BOTÃO: PROCESSAR SAQUE VIA API
                // ========================================
                Action::make('approve_payment')
                    ->label('Processar via API')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(function(Withdrawal $withdrawal): bool {
                        if ($withdrawal->status != 0) {
                            return false;
                        }
                        
                        $bankInfo = json_decode($withdrawal->bank_info, true);
                        if (!empty($bankInfo['transaction_id'])) {
                            return false;
                        }
                        
                        return true;
                    })
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('Digite a senha de aprovação')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (Withdrawal $withdrawal, array $data) {
                        $withdrawalPassword = \DB::table('aprove_withdrawals')->value('approval_password');

                        if (!Hash::check($data['password'], $withdrawalPassword)) {
                            Notification::make()
                                ->title('Senha Incorreta')
                                ->danger()
                                ->body('A senha de aprovação está incorreta.')
                                ->send();
                            return;
                        }

                        // Redirecionar para a rota de processamento
                        $url = route(\Helper::GetDefaultGateway() . '.withdrawal', [
                            'id' => $withdrawal->id, 
                            'action' => 'user'
                        ]);
                        
                        Notification::make()
                            ->title('Processando Saque')
                            ->success()
                            ->body('Saque de R$ ' . number_format($withdrawal->amount, 2, ',', '.') . ' está sendo processado via API.')
                            ->send();
                        
                        // Redirecionar
                        redirect($url);
                    }),

                // ========================================
                // BOTÃO: MARCAR COMO PAGO MANUALMENTE
                // ========================================
                Action::make('mark_as_paid')
                    ->label('Marcar como Pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar saque como pago?')
                    ->modalDescription('Esta ação marcará o saque como pago manualmente. Use apenas se o pagamento foi feito fora da plataforma.')
                    ->modalSubmitActionLabel('Sim, marcar como pago')
                    ->visible(fn(Withdrawal $withdrawal): bool => $withdrawal->status == 0)
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('Senha de Aprovação')
                            ->password()
                            ->required(),
                        Forms\Components\Textarea::make('observation')
                            ->label('Observação (opcional)')
                            ->placeholder('Ex: Pagamento feito via transferência manual')
                            ->rows(3),
                    ])
                    ->action(function (Withdrawal $withdrawal, array $data) {
                        $withdrawalPassword = \DB::table('aprove_withdrawals')->value('approval_password');

                        if (!Hash::check($data['password'], $withdrawalPassword)) {
                            Notification::make()
                                ->title('Senha Incorreta')
                                ->danger()
                                ->body('A senha está incorreta.')
                                ->send();
                            return;
                        }

                        // Marcar como pago
                        $withdrawal->update([
                            'status' => 1,
                            'bank_info' => json_encode([
                                'manual_payment' => true,
                                'paid_at' => now()->format('Y-m-d H:i:s'),
                                'paid_by' => auth()->user()->name ?? 'Sistema',
                                'amount' => $withdrawal->amount,
                                'observation' => $data['observation'] ?? 'Pagamento manual'
                            ])
                        ]);

                        Notification::make()
                            ->title('Saque Marcado como Pago')
                            ->success()
                            ->body('R$ ' . number_format($withdrawal->amount, 2, ',', '.') . ' foi marcado como pago.')
                            ->send();
                    }),

                // ========================================
                // BOTÃO: AGUARDANDO WEBHOOK
                // ========================================
                Action::make('processing')
                    ->label('Aguardando Webhook...')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->disabled()
                    ->visible(function(Withdrawal $withdrawal): bool {
                        if ($withdrawal->status != 0) {
                            return false;
                        }
                        
                        $bankInfo = json_decode($withdrawal->bank_info, true);
                        return !empty($bankInfo['transaction_id']);
                    }),

                // ========================================
                // BOTÃO: DEVOLVER SALDO
                // ========================================
                Action::make('return_balance')
                    ->label('Devolver Saldo')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Devolver saldo ao usuário?')
                    ->modalDescription('Esta ação irá cancelar o saque e devolver o valor ao saldo do usuário.')
                    ->modalSubmitActionLabel('Sim, devolver')
                    ->visible(fn(Withdrawal $withdrawal): bool => $withdrawal->status == 0)
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('Senha de Aprovação')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (Withdrawal $withdrawal, array $data) {
                        $withdrawalPassword = \DB::table('aprove_withdrawals')->value('approval_password');

                        if (!Hash::check($data['password'], $withdrawalPassword)) {
                            Notification::make()
                                ->title('Senha Incorreta')
                                ->danger()
                                ->body('A senha está incorreta.')
                                ->send();
                            return;
                        }

                        // Devolver saldo à wallet
                        $wallet = \App\Models\Wallet::where('user_id', $withdrawal->user_id)->first();
                        
                        if (!$wallet) {
                            Notification::make()
                                ->title('Erro')
                                ->danger()
                                ->body('Carteira do usuário não encontrada.')
                                ->send();
                            return;
                        }

                        $wallet->increment('balance', $withdrawal->amount);

                        // Atualizar status para cancelado
                        $withdrawal->update([
                            'status' => 2,
                            'bank_info' => json_encode([
                                'cancelled_at' => now()->format('Y-m-d H:i:s'),
                                'cancelled_by' => auth()->user()->name ?? 'Sistema',
                                'amount_returned' => $withdrawal->amount,
                                'reason' => 'Saldo devolvido manualmente'
                            ])
                        ]);

                        Notification::make()
                            ->title('Saldo Devolvido')
                            ->success()
                            ->body('R$ ' . number_format($withdrawal->amount, 2, ',', '.') . ' devolvido ao usuário.')
                            ->send();
                    }),

                // ========================================
                // BOTÃO: EXCLUIR REGISTRO
                // ========================================
                Action::make('delete_direct')
                    ->label('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir saque?')
                    ->modalDescription('Esta ação NÃO devolverá o saldo ao usuário. Apenas excluirá o registro.')
                    ->modalSubmitActionLabel('Sim, excluir')
                    ->visible(fn(Withdrawal $withdrawal): bool => $withdrawal->status != 1)
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('Senha de Aprovação')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (Withdrawal $withdrawal, array $data) {
                        $approvalSettings = AproveSaveSetting::first();
                        $inputPassword = $data['password'] ?? '';

                        if (!Hash::check($inputPassword, $approvalSettings->approval_password_save)) {
                            Notification::make()
                                ->title('Senha Incorreta')
                                ->danger()
                                ->body('A senha está incorreta.')
                                ->send();
                            return;
                        }

                        $withdrawal->delete();

                        Notification::make()
                            ->title('Registro Excluído')
                            ->success()
                            ->body('O saque foi excluído.')
                            ->send();
                    }),

                // ========================================
                // BOTÃO DE EDITAR REMOVIDO (conforme solicitado)
                // ========================================
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Confirme a exclusão em massa')
                        ->modalSubheading('Por favor, insira sua senha para confirmar a exclusão em massa.')
                        ->modalButton('Excluir Selecionados')
                        ->form([
                            TextInput::make('approval_password_bulk_delete')
                                ->password()
                                ->required()
                                ->label('Senha de Aprovação')
                                ->helperText('Digite a senha de aprovação para confirmar a exclusão em massa.')
                        ])
                        ->action(function ($records, array $data) {
                            $approvalSettings = AproveSaveSetting::first();
                            $inputPassword = $data['approval_password_bulk_delete'] ?? '';

                            if (!Hash::check($inputPassword, $approvalSettings->approval_password_save)) {
                                Notification::make()
                                    ->title('Erro de Autenticação')
                                    ->body('Senha incorreta. Por favor, tente novamente.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            foreach ($records as $record) {
                                $record->delete();
                            }

                            Notification::make()
                                ->title('Registros Excluídos')
                                ->body('Os registros selecionados foram excluídos com sucesso.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\WithdrawalResource\Pages\ListWithdrawals::route('/'),
            'create' => \App\Filament\Admin\Resources\WithdrawalResource\Pages\CreateWithdrawal::route('/create'),
            'edit' => \App\Filament\Admin\Resources\WithdrawalResource\Pages\EditWithdrawal::route('/{record}/edit'),
        ];
    }
}