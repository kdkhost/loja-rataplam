# Feature gate do Mercado Pago

O deploy do código e a execução das migrations não ativam a integração nova. As colunas `sandbox_enabled` e `production_enabled` são independentes, obrigatórias e criadas com valor `false`, inclusive para registros existentes.

Salvar chaves, tokens, collectors, segredos e opções de pagamento também não ativa o gateway nem altera a configuração legada. O fluxo novo nunca consulta `payment_settings`; a compatibilidade legada existe somente no controller legado, selecionado explicitamente pelo dispatcher quando nenhum ambiente V2 está habilitado.

## Ordem operacional

1. Fazer deploy e executar a migration pelo plano aprovado separadamente.
2. Salvar a configuração do ambiente sem ativá-lo.
3. Confirmar Public Key, Access Token, Collector ID, segredo de webhook e pelo menos Pix ou cartão habilitado.
4. Usar a ação administrativa explícita para ativar somente sandbox ou somente produção.
5. Validar checkout e webhook no ambiente autorizado.

Um gate ligado com configuração incompleta, ambiente desconhecido ou falha de descriptografia permanece fail-closed. O checkout V2 e o webhook V2 são bloqueados antes de chamadas remotas ou escritas. A resposta pública é genérica e não expõe readiness, tokens ou segredos.

O rollback operacional preferencial é desativar explicitamente o ambiente. A desativação interrompe novas operações sem apagar credenciais, ações, IDs ou dados de conciliação. Não se deve remover tabelas como mecanismo de rollback.
