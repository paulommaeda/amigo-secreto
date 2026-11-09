# WP Amigo Secreto Pro

O organizador cria um sorteio pelo painel público e compartilha o convite por link ou WhatsApp. Cada participante recebe um PIN ao se inscrever. Com pelo menos três participantes, o organizador pode sortear, enviar os resultados por e-mail e reabrir as inscrições se precisar refazer o sorteio.

## Instalação

1. [Baixe o ZIP do plugin](https://github.com/paulommaeda/amigo-secreto/archive/refs/heads/main.zip).
2. No WordPress, abra **Plugins → Adicionar plugin → Enviar plugin**. Envie o ZIP e ative o plugin.
3. Para configurar o site completo, siga a seção abaixo. Para usar apenas o sistema de sorteio, coloque `[amigo_secreto_dashboard]` na página do painel. O tutorial usa `[amigo_secreto_tutorial]` na página `/como-funciona/`. As páginas de convite são criadas ao abrir cada sorteio.

Requer WordPress 5.8 e PHP 7.4 ou superiores. Se estiver migrando da versão 22.0, desative a cópia antiga antes de ativar o ZIP. A nova versão usa as tabelas existentes e aplica a migração ao carregar pela primeira vez.

## Página inicial e blog

Em **Configurações → Amigo Secreto**, clique em **Ativar site do Amigo Secreto**. O plugin prepara a página inicial pública e o blog, reaproveita o painel e o tutorial quando encontra os shortcodes nas páginas existentes e liga o menu às páginas corretas.

- **Entrar** e **Criar conta** têm páginas próprias. Pelo botão **Criar sorteio**, o cadastro leva à criação do grupo após o acesso.
- Publique pelo menu **Posts**. A página inicial mostra os últimos artigos. O blog usa imagens destacadas, categorias, tags, busca e paginação do WordPress.
- O site aplica layouts próprios à home, ao painel, aos convites, aos artigos, aos arquivos do blog e à página 404.

O botão **Desativar layouts do site** recupera a configuração anterior de leitura nas páginas que o plugin definiu. As páginas criadas e os posts ficam disponíveis no WordPress.

Em sites com os layouts ativos, a versão 22.4.2 prepara as páginas institucionais que estiverem faltando, incluindo **Contato**, no primeiro carregamento do WordPress. Sobre, Contato, Termos de Uso e Política de Privacidade ficam ligados na home e seus textos podem ser editados em **Páginas**. Use **Configurações → Amigo Secreto → Dados públicos de contato** para atualizar o telefone ou publicar rua e CEP de um endereço comercial. O painel **Adicionar novas páginas** pode completar as páginas de acesso e repor páginas institucionais ausentes.

## SEO e acesso por agentes

Com os layouts ativos e o site visível para buscadores, o plugin declara em `robots.txt` as preferências de busca, uso em respostas de IA e treinamento. A resposta virtual do WordPress tem cache de até cinco minutos. Depois da atualização, limpe também o cache da hospedagem ou CDN se a auditoria ainda receber o arquivo antigo. O sitemap nativo usa as datas das páginas e publicações. Login, cadastro, painel e convites ficam fora do sitemap e têm sinal de desindexação. A home, os artigos e as páginas institucionais recebem metadados de busca e compartilhamento e dados estruturados. Se houver um plugin de SEO compatível ativo, ele controla os metadados HTML.

- **`/llms.txt`** lista as páginas públicas e os artigos recentes.
- **`/index.md`** oferece a versão Markdown da home. A home também responde em Markdown quando o cabeçalho `Accept: text/markdown` é preferido.
- **`/mcp`** atende clientes MCP por Streamable HTTP. O Server Card fica em **`/mcp/server-card`** e em **`/.well-known/mcp/server-card.json`**. O catálogo de descoberta fica em **`/.well-known/ai-catalog.json`**.
- Em navegadores compatíveis, o WebMCP registra duas ferramentas na home. Elas consultam o guia do sorteio e pesquisam artigos públicos.

Um arquivo físico na raiz do servidor tem prioridade sobre o `/llms.txt` gerado pelo WordPress. Se ele foi criado pelo Hostinger Tools, abra **Hostinger → Tools → LLM Optimization** e desative **Create LLMs.txt file**. A Hostinger remove o arquivo que ela gerou e a URL passa a servir o índice dinâmico do Amigo Secreto. **Configurações → Amigo Secreto** mostra um aviso quando há um arquivo estático na raiz. [Documentação da Hostinger](https://www.hostinger.com/support/how-to-enable-llms-txt-on-your-website/)

Depois de atualizar, limpe o cache de páginas. Se o LiteSpeed servir a versão HTML em cache antes do WordPress para pedidos Markdown, adicione esta regra no `.htaccess` da raiz, antes do bloco do LiteSpeed Cache. A regra deixa o cache da home habilitado para visitas que pedem HTML.

```apache
<IfModule LiteSpeed>
RewriteEngine On
RewriteCond %{REQUEST_URI} ^/$
RewriteCond %{HTTP:Accept} text/markdown [NC]
RewriteRule ^ - [E=Cache-Control:no-cache]
</IfModule>
```

Para verificar, faça uma requisição com `curl -i -H 'Accept: text/markdown' https://seuamigosecreto.com.br/` e confira `Content-Type: text/markdown`.

## FluentCRM

Quando o FluentCRM está ativo, cada inscrição no sorteio sincroniza nome, e-mail, WhatsApp e tags do grupo. Novos contatos entram com status transacional. Se a pessoa marcar a opção de receber dicas e novidades, a inscrição pode receber o status de inscrito e a tag **Amigo Secreto - Novidades**. O fluxo respeita contatos com descadastro, bloqueio ou reclamação. As novas notas do CRM registram o grupo e a escolha de novidades. O PIN é enviado na confirmação da inscrição pelo e-mail do WordPress.

## Atualizações pelo WordPress

Para publicar uma nova versão, atualize o cabeçalho `Version` e a constante `WAS_VERSION` em `wp-amigo-secreto.php` no mesmo commit que contém as mudanças. Envie esse commit para a branch `main`. O WordPress compara a versão publicada e oferece **Atualizar agora** em **Painel → Atualizações** e em **Plugins**.

O pacote oferecido vem do commit da versão detectada. A consulta ao GitHub usa cache de uma hora. **Painel → Atualizações → Verificar novamente** limpa o cache da consulta do plugin. Em **Plugins**, também é possível optar pelas atualizações automáticas do WordPress.
