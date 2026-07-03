# Loja Rataplam

Bem-vindo ao repositório principal do **Loja Rataplam**, uma plataforma completa de comércio eletrônico (e-commerce) construída com as melhores práticas de desenvolvimento web para oferecer performance, escalabilidade e uma excelente experiência de usuário.

---

## 💻 Sobre o Projeto

O **Loja Rataplam** é um sistema de Loja Virtual completo, projetado para suportar múltiplos gateways de pagamento, controle de estoque avançado, layout responsivo e otimizado (PWA) e painel administrativo rico em recursos (Painel Admin). 

O código-fonte foi cuidadosamente otimizado para a realidade do mercado brasileiro, com todas as traduções nativas e configurações de suporte rápido integradas (ex: WhatsApp, Correios e popups promocionais).

### Principais Recursos:
- **Painel Administrativo:** Gestão de produtos, pedidos, clientes e configurações globais.
- **Múltiplos Pagamentos:** Integração com Mercado Pago, PayPal, Stripe, Razorpay, Mollie, entre outros.
- **PWA (Progressive Web App):** Permite que os clientes instalem a loja como um aplicativo em seus celulares.
- **Integração Logística:** Configurações base preparadas para Correios e métricas de frete.
- **Suporte Fluido:** Botões flutuantes para WhatsApp e Popups configuráveis para saída ou promoção.

---

## 🛠️ Tecnologias e Versões (Plataforma)

Este sistema é fundamentado nas seguintes tecnologias:

- **PHP:** `^8.1`
- **Framework:** Laravel `^10.0`
- **Banco de Dados:** MySQL ou MariaDB (Compatível)
- **Frontend Admin/Loja:** Blade Templates, CSS3, JavaScript e Componentes Bootstrap.
- **Gerenciador de Dependências:** Composer

---

## 📋 Requisitos do Servidor

Para rodar o sistema, seu servidor web (Apache, Nginx, etc.) precisa atender aos seguintes requisitos mínimos:

- PHP >= 8.1
- Extensão PHP BCMath
- Extensão PHP Ctype
- Extensão PHP Fileinfo
- Extensão PHP JSON
- Extensão PHP Mbstring
- Extensão PHP OpenSSL
- Extensão PHP PDO
- Extensão PHP Tokenizer
- Extensão PHP XML
- Banco de Dados MySQL / MariaDB

---

## 🚀 Como Instalar

Você pode instalar a plataforma em qualquer servidor de hospedagem padrão ou em localhost (XAMPP/WAMP/Herd). O sistema utiliza uma estrutura web segura onde a raiz intercepta as requisições, mas o núcleo de proteção fica oculto.

### Passo a passo para desenvolvimento local:

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/SEU-USUARIO/loja-rataplam.git
   cd loja-rataplam
   ```

2. **Instale as dependências:**
   Navegue até a pasta `core` (onde fica o núcleo Laravel) e instale via Composer:
   ```bash
   cd core
   composer install
   ```

3. **Configuração de Ambiente (.env):**
   Copie o arquivo de exemplo para criar suas credenciais:
   ```bash
   cp .env.example .env
   ```
   Abra o arquivo `.env` gerado na pasta `core` e edite as credenciais de banco de dados (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) e defina a URL do seu sistema (`APP_URL`).

4. **Gerar a Chave da Aplicação:**
   ```bash
   php artisan key:generate
   ```

5. **Migrações de Banco de Dados:**
   Construa a estrutura de banco de dados (se você tiver o banco de dados limpo criado):
   ```bash
   php artisan migrate
   ```
   *(Caso utilize Seeders para popular a loja, rode: `php artisan db:seed`)*

6. **Permissões de Pastas:**
   Certifique-se de que o servidor tenha permissão de escrita nas pastas:
   - `core/storage`
   - `core/bootstrap/cache`

7. **Acesso Web:**
   Se estiver usando um servidor local como XAMPP, acesse `http://localhost/loja-rataplam`. O arquivo `index.php` na raiz fará o roteamento automático para o Laravel.

### Instalação via Assistente Web (Hospedagem)
Se você subiu o projeto direto para uma hospedagem Cpanel (ou similar), e tentar acessar o site sem banco configurado, o próprio pacote `Laravel Installer` deverá interceptar a página inicial guiando você por um passo a passo visual no navegador para setar banco de dados e credenciais de admin!

---

## 📂 Organização de Diretórios

O projeto foge de uma estrutura Laravel convencional pura para facilitar a instalação em hospedagens compartilhadas comuns:

- `/core`: Contém todo o ecossistema Laravel e o backend (MVC, rotas, views do Blade).
- `/assets`: Arquivos estáticos globais usados abertamente (imagens, ícones, temas).
- `/installer`: Arquivos relacionados ao processo de instalação via assistente web.
- `index.php` (Raiz): Arquivo de gatilho principal que carrega os `autoloads` da pasta `/core`.

---

## 🔔 Atualizações Recentes (Julho 2026)

- **Correção de Acentuação e Regionalização:** Revisão profunda em views (como WhatsApp, PWA, Popups e Correios) e Controllers para garantir português brasileiro correto (Olá, horário, será, rápido, etc).
- **Formatação de Arquivos:** Repositório higienizado e unificado para não conter arquivos com codificação *UTF-8 with BOM*. Apenas *UTF-8* puro.
- **Ocultação Inteligente de Idioma:** O seletor de linguagem do portal só é ativado quando o painel registrar mais de um idioma habilitado.
- **GitIgnore Otimizado:** Ignorando nativamente dependências pesadas de `/core/vendor` para commits mais rápidos e leves.
