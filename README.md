# Módulo de integração Paggi para PrestaShop

### Compatibilidade: 1.6 e 1.7

Faça o download da última versão release (como arquivo zip) [Versoes Release]: https://github.com/paggi-com/plugin-prestashop/releases

Acessa a área administrativa da sua loja virtual.
Vá em: "Módulos" -> "Módulos e Serviços" -> "Adicionar novo módulo", faça o upload do arquivo paggi.zip



Após a conclusão da instalação do módulo de pagamento Paggi, será necessário configurá-lo.

### Configuração do módulo
- Imagem

A imagem que representa o checkout (cartão crédito) de pagamento pode ser substituída. Basta clicar em `adicionar arquivo` e selecionar a imagem desejada.

- Api Key (Produção)

Informe o Token de produção.

- Api Key (Desenvolvimento)

Informe o Token de desenvolvimento ou utilize seguinte token: `B31DCE74-E768-43ED-86DA-85501612548F`. Todas as transações realizadas com essa chave estarão em modo de demonstração e não será cobrado nenhum valor de qualquer cartão.

- Selecione o ambiente

`Produção`, para que as transações possam ser processadas e cobradas de forma real.

`Desenvolvimento`, Realize todos os testes necessários em nossa API.

**Após configurar esses campos clique em ``Salvar``**

### Opções de parcelamento

`Parcelamento sem juros`, número de parcelas limite para cobrança sem juros.

`Número máximo de parcelas`, informe o número máximo de parcelas possíveis com pagamento via cartão de crédito.

`Taxa de juros`, informe qual a taxa de juros por parcela.

**Após configurar esses campos clique em ``Salvar``**

### Status da transação

O PrestaShop precisa reconhecer e saber do que se trata cada status retornado pelo módulo Paggi. Para isso é necessário mapear os status Paggi com os status PrestaShop. 

**Status PAGGI**

**(1) Aprovado/Approved** - Transação enviada a adquirente com sucesso.

**(2) Recusado** - Transação recusada pela adquirente

**(3) Registrado/** - Transação salva no sistema mas não foi enviada para adquirente ou análise de risco

**(4) Pré aprovado** - Transação autorizada pela adquirente mas não confirmada

**(5) Crédito aprovado** - Transação autorizada pela análise de risco mas nao enviada a adquirente.

**(6) Com Risco/Not Cleared** - Transação recusada pela análise de risco

**(7) Revisão Manual/Manual clearing** - Transação pré aprovada para revisão. 

**(8) Capturado/Capture** - Transação previamente autorizada foi confirmada com a adquirente

**(9) Cancelado/Cancelled** - Solicitação de cancelamento foi enviado a adquirente (confirmação em até 7 dias)

**(10) Chargeback/Chargeback** - Transação não reconhecida pelo dono do cartão

**Sugestão de status PrestaShop (para mapeamento com Paggi)** 

**(1, 8) Aprovado**

**(2, 6) Recusado**

**(3) Processando**

**(7) Em análise**

**(8) Em análise**

**(9) Em análise**

**(10) Em análise**


**Após configurar esses campos clique em ``Salvar``**



