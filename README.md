
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

# CONFIGURAÇÕES NECESSÁRIAS NO PHP PARA SUPORTAR O UPLOAD DOS LOGS:
    - post_max_size = 12M
    - upload_max_filesize = 12M
