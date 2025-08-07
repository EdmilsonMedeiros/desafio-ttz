
# PROJETO DESAFIO TTZ

## TECNOLOGIAS UTILIZADAS:
    - PHP
    - Laravel
    - VueJS
    - TAILWIND CSS
    - MYSQL

## FUNCIONALIDADES:
    - IMPORTAÇÃO DE ARQUIVO DE LOGS
    - DASHBOARD COM DADOS EXTRAÍDOS DA BASE:
    Total de jogadores ativos no intervalo.
       - Pontuação total acumulada;
       - Itens mais coletados;
       - Desempenho dos jogadores;
       - Chefes derrotados.

    - API COM:
       - GET /players → lista de jogadores com dados básicos;
       - GET /players/:id/stats → estatísticas de um jogador (pontuação, mortes, itens coletados, quests concluídas);
       - GET /leaderboard → ranking de jogadores por pontuação;
       - GET /events?limit=50 → últimos eventos;
       - GET /items/top → itens mais coletados.

_Obs: como o arquivo é grande, ao importar o arquivo, o processo de leitura dos dados e escrita na base demora um pouco e fica em fila de execução, o que pode passar a impressão de que nada aconteceu ao importar, mas como a mensagem retornada informa, esses dados ficam em processamento._

## CONFIGURAÇÕES NECESSÁRIAS NO php.ini PARA SUPORTAR O UPLOAD DOS LOGS:
    post_max_size = 12M
    upload_max_filesize = 12M

## SCRIPT DE DEPLOY:
*CLONE O REPOSITÓRIO:*
    
    git clone <nome_repo...>

*EXECUTE O COMANDO PARA INSTALAÇÃO DO COMPOSER:*

    composer install 

*EXECUTE O COMANDO PARA A INSTALAÇÃO DOS PACOTES NPM:*

    npm install

*CONFIGURE O BANCO APARTIR DE UMA CÓPIA DO .env.example COMO .env:*

    DB_CONNECTION=mysql
    DB_HOST=192.168.0.26
    DB_PORT=3306
    DB_DATABASE=ttz_project
    DB_USERNAME=root
    DB_PASSWORD=dev

*PARA SERVIR A APLICAÇÃO EM MODO DE DESENVOLVIMENTO, DEIXE EXECUTANDO OS 3 COMANDOS ABAIXO VIA TERMINAL NA RAIZ DO PROJETO:*
    
    npm run dev
<br>

    php artisan serve
<br>

    php artisan queue:work