# Changelog

Todas as modificacoes relevantes do sistema devem ser registradas neste arquivo.

Formato padrao:
- `Adicionado` para novas funcionalidades.
- `Alterado` para ajustes em funcionalidades existentes.
- `Corrigido` para bugs resolvidos.
- `Banco de dados` para migrations, tabelas e campos novos.
- `Validacao` para comandos executados e observacoes tecnicas.

## 2026-07-05 - Popup de comunicacao reabilitado

### Corrigido
- Popup configurado em `/admin/announcement` voltou a abrir no site publico com a estrutura esperada pelo frontend.
- Layout do popup permaneceu com largura e altura controladas, sem perder o gatilho de abertura.

### Validacao
- `php artisan view:cache` executado com sucesso.
- `php -l resources/views/includes/announcement-popup.blade.php` executado com sucesso.

## 2026-07-05 - Popup de comunicacao restaurado

### Corrigido
- Popup de comunicacao voltou a abrir normalmente apos a simplificacao do include.
- Tamanho do popup foi mantido ajustado, com largura e altura limitadas no desktop e no mobile.

### Validacao
- `php artisan view:cache` executado com sucesso.
- `php -l resources/views/includes/announcement-popup.blade.php` executado com sucesso.

## 2026-07-05 - Popup de comunicacao volta a renderizar sem imagem

### Corrigido
- Popup de comunicacao/announcement deixou de sumir quando a imagem nao esta disponivel ou nao pode ser resolvida.
- Modal agora exibe um fallback visual com titulo e resumo, mantendo a comunicacao visivel mesmo sem imagem.

### Validacao
- `php artisan view:cache` sera executado para validar a Blade revisada.

## 2026-07-05 - Ajuste de dimensao do popup de comunicacao

### Corrigido
- Popup de comunicacao/announcement passou a respeitar largura e altura maximas mais apropriadas para desktop e mobile.
- Conteudo textual do popup voltou a ser exibido corretamente ao lado da imagem, sem ficar espremido ou escondido.
- Fluxo de newsletter do popup foi preservado quando o tipo configurado for `newletter`.

### Validacao
- `php artisan view:cache` executado com sucesso.
- Estrutura de popup revisada em `core/resources/views/includes/announcement-popup.blade.php`.

## 2026-07-05 - Contraste da sidebar administrativa

### Alterado
- Textos, icones, submenus, indicadores e estados ativo/hover da sidebar administrativa foram padronizados para tema claro e tema escuro.
- Subitens do menu administrativo passaram a ter cor explicita no tema escuro, evitando baixa leitura por heranca do template base.

### Validacao
- `git diff --check` executado sem erros de espaco em branco.
- `php artisan view:cache` executado com sucesso para validar que as views continuam compilando.

## 2026-07-05 - Reformulacao visual da Central Interna de Cron

### Alterado
- Pagina `/admin/internal-cron` foi reorganizada com cabecalho, indicadores rapidos, comando geral da hospedagem, formulario de nova tarefa e cards individuais por rotina.
- Tarefas registradas passaram a exibir status, tipo, proxima execucao, ultima execucao, expressao cron e saida operacional em blocos mais legiveis.
- Agendamento no padrao cPanel recebeu preview dinamico da expressao cron e botao para copiar o comando geral da hospedagem.
- Layout da Central Interna de Cron foi ajustado para responsividade e compatibilidade com tema claro/escuro do painel.

### Validacao
- `php artisan view:cache` executado com sucesso.
- `php -l resources/views/back/platform/cron.blade.php` executado com sucesso.

## 2026-07-05 - Timezone brasileiro

### Alterado
- Timezone padrao do Laravel passou a usar `APP_TIMEZONE`, com fallback em `America/Sao_Paulo`.
- Scheduler do Laravel passou a executar eventos usando explicitamente o timezone configurado da aplicacao.
- Ambiente local e configuracao PHP de hospedagem passaram a declarar `America/Sao_Paulo`.

### Validacao
- `php artisan config:clear` executado para remover configuracao antiga em cache.
- `php artisan tinker --execute="echo config('app.timezone').'|'.now()->timezone->getName().'|'.now()->format('d/m/Y H:i:s');"` confirmou `America/Sao_Paulo`.
- `php artisan schedule:list` executado com sucesso, confirmando o carregamento do scheduler.
- `php -l app/Console/Kernel.php` executado com sucesso.

## 2026-07-05 - Analitico, SEO por produto, crons cPanel, popups e rodape publico

### Adicionado
- Analitico publico robusto com rastreamento de visualizacoes, visitantes, paginas, produtos, dispositivos, origens e eventos de interacao.
- Evento publico `/analytics/event` para registrar busca, clique em produto, favoritos, comparar, adicionar ao carrinho e comprar agora.
- Pagina administrativa individualizada `/admin/analytics` com graficos de trafego, dispositivos, origens, produtos mais vistos, paginas mais acessadas e saude SEO.
- SEO individual por produto com titulo SEO, palavra-chave foco, canonical, robots, Open Graph, Twitter/X, pontuacao, checklist e preview.
- Schema.org `Product` em JSON-LD na pagina publica de cada produto.
- Tarefas padrao do sistema na Central Interna de Cron: fila, cache, views, rotas, configuracoes e storage.
- Comando `seo:recalculate-products` para recalcular a pontuacao SEO dos produtos existentes.
- Agendamento de crons no formato WHM/cPanel por selects de minuto, hora, dia, mes e dia da semana.
- Presets de cron iguais ao cPanel para minuto, 5/10/15/30 minutos, horario, diario, semanal e mensal.

### Alterado
- Dashboard passou a manter apenas os cards rapidos do analitico publico, com link para o analitico completo.
- Central Interna de Cron passou a editar cada rotina em linha, com status, proxima execucao, ultima execucao e execucao manual.
- Menu administrativo ganhou acesso direto a pagina de Analitico.
- Rodape publico passou a exibir os creditos de desenvolvimento para Marcelo Brad - RJ e Eth Estrategias com links oficiais.

### Corrigido
- Popups publicos de anuncio/newsletter, PWA, promocao e saida agora usam fila unica, impedindo sobreposicao e conflito visual.
- Meta tags antigas da pagina de produto foram corrigidas para usar os campos reais do produto e os dados SEO configurados.
- Analise SEO deixou de gravar checklist em JSON duplicado, preservando o cast correto do model.
- Retorno de paginas mais acessadas no servico analitico foi ajustado para array, evitando erro de tipo.

### Banco de dados
- `2026_07_05_000012_create_analytics_tables_and_product_seo_fields`
- `2026_07_05_000013_add_cpanel_schedule_to_internal_cron_tasks_table`

### Validacao
- `php artisan migrate --force` executado para analitico, SEO e crons cPanel.
- `php artisan route:list --path=analytics` executado com sucesso.
- `php artisan route:list --path=internal-cron` executado com sucesso.
- `php artisan view:cache` executado com sucesso.
- `php artisan seo:recalculate-products --chunk=200` executado, recalculando 336 produtos.
- `php artisan test` executado com sucesso: 2 testes passaram.
- `php artisan view:clear` executado antes do fechamento.
- `php -l` executado nos controllers, services, commands, models e migrations alterados.

## 2026-07-04 - Loja publica, checkout brasileiro, Mercado Pago Pix e organizacao operacional

### Adicionado
- Cadastro administrativo de paises de venda, com ativar/desativar e filtro no checkout e nos enderecos do cliente.
- Suporte a Pix no Mercado Pago com QR Code, codigo copia e cola, expiracao configuravel e webhook de confirmacao.
- Configuracao robusta do Mercado Pago no painel para Pix, cartao de credito, parcelas, repasse de taxa, taxa percentual e taxa fixa.
- Campos `gateway_fee` e `payment_details` nos pedidos para armazenar taxa repassada e dados do Pix/Mercado Pago.
- Controller para a rota publica `/vendor`, preservando a rota existente e corrigindo a listagem/cache de rotas.
- Scripts operacionais em `core/scripts/` para importar, organizar e complementar produtos antigos da Rataplam com descricoes, atributos, especificacoes, cores e tamanhos.
- Menu administrativo "Paises de venda" para gerenciar os paises ativos no checkout.

### Alterado
- Modal do Mercado Pago foi padronizado para PT-BR, CPF/CNPJ, Pix e cartao de credito.
- Checkout, cadastro de endereco e validacoes passaram a aceitar apenas paises ativos configurados no painel.
- Pagina de sucesso do checkout passou a exibir instrucoes de Pix quando o pedido for criado como pendente.
- Filtro de preco da loja foi ajustado para funcionar com slider e fallback por formulario GET.
- Cards de produtos, ofertas e campanhas receberam padronizacao visual para altura, imagem, conteudo e responsividade.
- Menu mobile publico passou a abrir em painel deslizante lateral com animacao.
- Barra superior mobile foi reduzida para rastreamento, favoritos e entrar.
- Seletor de moeda passou a ficar oculto quando houver apenas um idioma ativo.
- Banners publicos passaram a ajustar contraste do texto automaticamente conforme o fundo da imagem.
- Imagem da pagina de manutencao passou a cobrir toda a area visual configurada.
- Dias e horarios de atendimento foram formatados no padrao brasileiro.
- Textos restantes do site publico, blog, contato, checkout, pedidos e includes foram revisados para PT-BR.

### Corrigido
- Falha do filtro de preco da loja que nao aplicava corretamente os valores minimo e maximo.
- Erro de PWA quando a extensao GD nao esta disponivel na instalacao PHP.
- Gatilho de banner/popup de saida que nao funcionava corretamente no sistema.
- Lista de paises do checkout removendo registros legados "Pais internacional" do fluxo publico.
- Texto reCAPTCHA restante em ingles no checkout.
- Modal antigo do Mercado Pago com textos em ingles, DNI e campos de cartao fora do padrao brasileiro.
- Cartao de debito bloqueado explicitamente no Mercado Pago, tanto no formulario quanto no backend.
- Rota `/vendor` que quebrava `php artisan route:list` por apontar para controller inexistente.
- Residuos de configuracao de outro gateway no JSON do Mercado Pago.

### Banco de dados
- `2026_07_04_000008_add_status_to_countries_table`
- `2026_07_04_000009_update_mercadopago_pix_settings`
- `2026_07_04_000010_add_gateway_fee_and_payment_details_to_orders_table`
- `2026_07_04_000011_clean_mercadopago_payment_settings`

### Validacao
- `php artisan migrate --force` executado para paises ativos, configuracao Mercado Pago Pix e campos novos de pedido.
- `php artisan route:list --name=mercadopago` executado com sucesso.
- `php artisan route:list --name=vendor.profile` executado com sucesso.
- `php artisan route:cache`, `php artisan view:cache`, `php artisan view:clear` e `php artisan optimize:clear` executados.
- `php -l` executado nos controllers, requests, models e migrations alterados.
- Validacao HTTP local em `/vendor` e `/catalog` retornando `200`.
- Validacao do modal Mercado Pago renderizado sem DNI e sem textos antigos em ingles.

## 2026-07-03 - Atualizacao geral do painel, PWA, popups, WhatsApp, Correios e operacoes

### Adicionado
- Integracao Correios Brasil no painel com modo oficial pago/contrato e modo gratuito/legado configuravel.
- Tela de configuracao dos Correios com ativar/desativar, CEP de origem, servicos, credenciais, token, endpoint legado e teste rapido.
- Central Interna de Cron com tarefas cadastradas no sistema e apenas um cron geral na hospedagem.
- Configuracao PWA pelo painel com ativar/desativar, nome, nome curto, cores, URL inicial, manifest e service worker dinamicos.
- Popup de instalacao do PWA no site publico, com ativar/desativar, titulo, texto, imagem, botoes e atraso.
- Geracao automatica de icones PWA `192x192` e `512x512`.
- Upload manual de icones PWA `192x192` e `512x512`.
- Campo `status` para idiomas e moedas, permitindo ativar/desativar opcoes sem perder o idioma/moeda cadastrado.
- Pagina de manutencao personalizada com contador regressivo, imagem, texto, liberacao por IP e codigo de dispositivo.
- Paginas de erro personalizadas `403`, `404`, `500` e `503` seguindo as cores do projeto.
- Tooltips personalizados no painel admin com suporte aos temas claro e escuro.
- Rodape do painel admin com versao do sistema, Laravel, banco de dados e PHP.
- Botao flutuante de suporte WhatsApp existente no painel admin com configuracao via painel.
- Lista dinamica de suportes do WhatsApp do painel admin, com adicionar/remover contatos.
- Botao flutuante WhatsApp do site publico configuravel pelo painel.
- Box de atendimento do WhatsApp publico no primeiro clique, exibindo foto do atendente, nome, status, cumprimento por horario e mensagem configurada.
- Configuracao de horario e dias de atendimento do WhatsApp publico pelo painel.
- Padrao global de upload por clique ou arrasta e solta para campos `.upload-photo` do painel admin, com preview imediato da imagem e compactacao automatica para imagens grandes.
- Indicacao global de dimensao ideal em cada campo de upload de imagem do painel, com regras por contexto para logo, favicon, PWA, WhatsApp, produtos, galerias, banners, categorias, marcas, servicos, posts, popups e imagens institucionais.
- Configuracao `.user.ini` em `core/` e `core/public/` para ampliar limites de upload em hospedagem compativel.
- Sidebar do painel admin reorganizada em grupos coerentes: Catalogo, Vendas e Pedidos, Operacao Comercial, Clientes e Atendimento, Marketing, Conteudo do Site, Configuracoes e Sistema.
- Popup promocional ativavel no site publico.
- Popup de saida ativavel para oferta de desconto/cupom.
- Popup promocional vinculado a produto existente, com imagem, nome, resumo, preco antigo, preco atual e link do produto.
- Campanhas promocionais do popup por tipo: promocao relampago, Black Friday ou personalizada.
- Temporizador de promocao com data/hora de inicio e fim, incluindo contador regressivo no popup publico.
- Importador de produtos do site antigo por arquivo CSV ou URL CSV, gravando na estrutura atual de `items`.
- Permissoes granulares para Correios, Popups Promocionais, WhatsApp Flutuante e Importar Produtos Antigos.

### Alterado
- Front-end publico, area do cliente, carrinho, checkout, pagamentos, pedidos, blog, tickets e includes foram revisados para obedecer ao idioma padrao PT-BR.
- `pt_website.json` foi recomposto em UTF-8 sem BOM, corrigindo mojibake e incluindo traducoes para placeholders, botoes, menus, mensagens, tooltips e textos alternativos de imagens.
- Dados publicos de demonstracao foram convertidos para PT-BR, incluindo configuracoes do site, produtos, categorias, subcategorias, banners, servicos, paginas, posts, FAQs, fretes, estados e paises.
- Idiomas do sistema foram limitados a Portugues e Ingles, com filtro de ativos no seletor publico e no painel.
- Moedas do sistema foram limitadas a BRL e USD, com filtro de ativos no seletor publico e no painel.
- Idioma arabe legado foi removido do banco por estar fora da regra atual de Portugues/Ingles e apontar para arquivo JSON inexistente.
- Menu lateral do painel passou a usar chaves de traducao em todos os grupos e itens novos, obedecendo dinamicamente o idioma padrao do dashboard.
- `pt_dashboard.json` foi atualizado em UTF-8 sem BOM, corrigindo mojibake e incluindo as novas chaves do menu, idioma e moeda.
- Idioma padrao do sistema passou a sincronizar moeda e formatacao quando PT-BR esta ativo.
- Moeda e formatacao brasileiras foram padronizadas para BRL, decimal `,` e milhar `.`.
- Mascaras brasileiras e ViaCEP foram aplicados nos formularios carregados por `br-localization.js`.
- Dropdown de notificacoes passou a remover/atualizar notificacoes lidas automaticamente.
- Confirmacoes administrativas foram padronizadas com SweetAlert2.
- Notificacoes administrativas foram padronizadas com notify/toastr, evitando `alert` nativo.
- Layout dark/light do painel recebeu correcoes visuais de navbar, cards, bubbles e formularios.
- Logo do painel foi ajustada para melhor legibilidade no tema escuro.
- Botao hamburger mobile do painel passou a abrir a sidebar como overlay, sem empurrar o conteudo.
- Manifest PWA passou a usar icones especificos `192x192` e `512x512` quando configurados.
- Botao WhatsApp do painel admin deixou de depender de `.env` quando configurado pelo painel.
- Botao WhatsApp do site publico recebeu CSS proprio fora do bloco de popups para sempre aparecer quando ativo.
- Botao WhatsApp do site publico deixou de abrir conversa direta no primeiro clique e passou a abrir o box de atendimento antes do link para iniciar conversa.
- `core/CHANGELOG.md` passou a manter o historico completo da atualizacao, sem depender apenas de apontamento para o changelog da raiz.
- Formulario de configuracao do WhatsApp flutuante passou a enviar arquivos com `multipart/form-data`.
- Campo global de upload por arrasta e solta recebeu novo visual sem o botao nativo `Browse`.
- Campos de upload de imagem sem classe `.upload-photo`, como galerias, tambem passaram a receber o padrao visual e a dimensao ideal quando usam `accept="image/*"`.
- Orientacoes antigas e conflitantes de tamanho de imagem foram removidas das views, mantendo a dimensao ideal padronizada somente pelo componente global de upload.
- Inputs de imagem que eventualmente nao estejam dentro do componente `.file` agora recebem uma indicacao inline de dimensao ideal.
- Itens antigos soltos do sidebar foram realocados para seus grupos correspondentes, mantendo as permissoes do menu de usuarios administrativos comuns.
- Lista de inscritos foi movida para Marketing; Clientes e Atendimento ficou apenas com clientes, chamados e WhatsApp.
- Fluxo de trabalho ajustado para nao gerar arquivos ZIP automaticamente.

### Corrigido
- Textos fixos de `alt`, tooltips e placeholders que ainda apareciam em ingles no front foram ajustados para usar as traducoes do idioma ativo.
- Erro `array_combine(): Argument #1 ($keys) must be of type array, null given` corrigido nas views de edicao de produtos, produtos digitais, afiliados, licenciados e no salvamento de traducoes.
- Seletores de idioma e moeda agora ignoram registros desativados ou nao permitidos, inclusive quando acessados por URL antiga.
- Edicao e configuracao de idiomas agora validam se o arquivo JSON existe antes de usar `file_get_contents`, evitando erro 500 quando um registro legado aponta para arquivo ausente.
- Upload de imagens quebradas corrigido com espelhamento de `storage/app/public` para `public/storage`.
- Notificacoes lidas deixam de aparecer na lista automaticamente.
- Problemas visuais de dark mode nos cards do dashboard foram corrigidos.
- Formato monetario do painel corrigido para exibicao brasileira.
- Exibicao do botao WhatsApp do site corrigida quando nao havia popup promocional ativo.
- Botao WhatsApp admin agora respeita ativar/desativar no painel e nao cria outro botao.
- WhatsApp publico agora exibe mensagem de fora do horario quando o atendimento estiver fechado.
- Foto do atendente do WhatsApp publico agora chega corretamente ao Laravel e pode ser gravada no banco.
- Upload da foto do atendente passou a evitar falha por limite de 2 MB usando compactacao no navegador antes do envio.
- Fallback quebrado `noimage.png` do atendente foi substituido por `placeholder.png`.

### Banco de dados
- `2026_07_02_000001_add_platform_infra_fields_to_settings_table`
- `2026_07_02_000002_create_internal_cron_tasks_table`
- `2026_07_02_000003_add_commerce_platform_fields_to_settings_table`
- `2026_07_02_000004_add_pwa_install_popup_fields_to_settings_table`
- `2026_07_02_000005_add_product_promo_popup_fields_to_settings_table`
- `2026_07_02_000006_add_admin_whatsapp_widget_fields_to_settings_table`
- `2026_07_02_000007_add_admin_whatsapp_contacts_to_settings_table`
- `2026_07_03_000001_add_site_whatsapp_widget_fields_to_settings_table`
- `2026_07_03_000002_add_status_to_languages_and_currencies`

### Validacao
- `php -l` executado nos controllers, services, models e migrations alterados.
- `php artisan migrate --force` executado para criar status de idiomas/moedas e normalizar PT/EN e BRL/USD.
- `php artisan view:cache` e `php artisan view:clear` executados apos ajustes de idioma, moeda e menu.
- `php artisan migrate --force` executado com sucesso nas migrations novas.
- `php artisan view:cache` executado com sucesso apos ajustes de Blade.
- `php artisan view:clear` executado antes de fechar a entrega.
- `node --check assets/back/js/custom.js` executado apos ajustes JS.

### Observacoes
- O aviso `PDO::MYSQL_ATTR_SSL_CA is deprecated since 8.5` continua vindo de `core/config/database.php:62` e e anterior as mudancas.
- Se o icone base do PWA for SVG, a geracao automatica de PNG nao rasteriza; nesse caso o admin deve enviar os icones manualmente.
- Para importar produtos antigos, e necessario fornecer CSV local ou URL CSV do site antigo.
