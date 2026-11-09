# WP Amigo Secreto Pro

O organizador cria um sorteio pelo painel público e compartilha o convite por link ou WhatsApp. Cada participante recebe um PIN ao se inscrever. Com pelo menos três participantes, o organizador pode sortear, enviar os resultados por e-mail e reabrir as inscrições se precisar refazer o sorteio.

## Instalação

1. [Baixe o ZIP do plugin](https://github.com/paulommaeda/amigo-secreto/archive/refs/heads/main.zip).
2. No WordPress, abra **Plugins → Adicionar plugin → Enviar plugin**. Envie o ZIP e ative o plugin.
3. Use `[amigo_secreto_dashboard]` na página do painel. O tutorial usa `[amigo_secreto_tutorial]` na página `/como-funciona/`. As páginas de convite são criadas ao abrir cada sorteio.

Requer WordPress 5.8 e PHP 7.4 ou superiores. Se estiver migrando da versão 22.0, desative a cópia antiga antes de ativar o ZIP. A nova versão usa as tabelas existentes e aplica a migração ao carregar pela primeira vez.

## Atualizações pelo WordPress

Para publicar uma nova versão, atualize o cabeçalho `Version` e a constante `WAS_VERSION` em `wp-amigo-secreto.php` no mesmo commit que contém as mudanças. Envie esse commit para a branch `main`. O WordPress compara a versão publicada e oferece **Atualizar agora** em **Painel → Atualizações** e em **Plugins**.

O pacote oferecido vem do commit da versão detectada. A consulta ao GitHub usa cache de uma hora. **Painel → Atualizações → Verificar novamente** limpa o cache da consulta do plugin. A instalação usa o fluxo padrão de atualizações do WordPress.
