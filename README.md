# Loja Rataplam

Repositório principal do sistema de loja virtual.

## Atualizações Recentes

### Correções de Acentuação e Padronização UTF-8 (Julho 2026)
- **Correção de Acentuação:** Várias palavras exibidas no site público e no painel admin (ex: "Ola", "horario", "proximo", "rapido") estavam sem acento no código-fonte. Foram substituídas por suas versões corretas no padrão brasileiro (Olá, horário, próximo, rápido).
- **Traduções do Painel (Controllers):** Textos de retorno de erro nos controllers como `PlatformController`, `CurrencyController` e `LanguageController` foram devidamente acentuados.
- **Formatação de Arquivos:** Todo o repositório foi escaneado para garantir que nenhum arquivo utilizasse a formatação **UTF-8 com BOM (Byte Order Mark)**, pois isso causava problemas de leitura em alguns servidores. O padrão oficial adotado no repositório é apenas **UTF-8**.
- **Ocultação de Idiomas:** O seletor de idiomas do site público foi ajustado para só aparecer se houver mais de um idioma ativo configurado no painel.

## Organização de Diretórios
- `core/`: Contém todo o ecossistema e lógica principal (Laravel).
- `assets/`: Arquivos estáticos globais (imagens, fontes e scripts).
- `installer/`: Arquivos de instalação do sistema.
