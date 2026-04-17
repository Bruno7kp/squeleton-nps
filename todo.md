# TODO - Plano de Producao NPS (Squeleton.dev)

## Objetivo
Entregar uma plataforma NPS em producao com PHP Slim, SQLite, HTMX, VanJS e Squeleton.dev, cobrindo home showcase, admin, widget embed, gatilhos e dashboard.

## Regras do projeto
- Sempre usar o nome Squeleton.dev (nunca Skeleton.dev).
- Priorizar classes utilitarias e bibliotecas integradas do Squeleton antes de criar CSS/JS novo.
- API JSON para widget e respostas HTMX para admin.

## Roadmap por fases

## Fase 0 - Fundacao tecnica
- [x] Definir estrutura de pastas (app, public, templates, database, scripts).
- [x] Configurar Slim com rotas web e API.
- [x] Configurar SQLite com conexao unica e camada de acesso.
- [x] Configurar Docker (PHP 8.x + Nginx) para ambiente local/producao.
- [x] Criar .env.example com ADMIN_USER, ADMIN_PASS e variaveis de app.
- [x] Criar comando unico de setup (Makefile ou setup.sh).

Criterio de aceite:
- [ ] Projeto sobe com um unico comando e responde uma rota healthcheck.

## Fase 1 - Banco de dados e seed
- [x] Modelar tabelas: admins, projects, surveys, questions, survey_rules, submissions, submission_answers.
- [x] Criar migracoes idempotentes.
- [x] Criar seed com dados realistas (projeto, pesquisas, perguntas e respostas).
- [x] Garantir indices minimos para consultas de dashboard.

Criterio de aceite:
- [x] Banco inicializa limpo e seed gera dados utilizaveis para demo.

## Fase 2 - Autenticacao e base do admin
- [ ] Implementar login/logout via $_SESSION.
- [ ] Middleware para proteger rotas /admin.
- [ ] Layout base do admin com Squeleton.dev e HTMX.
- [ ] Alertas de erro nativos + toast de sucesso (Toastify).

Criterio de aceite:
- [ ] Usuario nao autenticado nao acessa /admin.

## Fase 3 - Gestao de projetos
- [ ] Tela de listagem de projetos (HTMX).
- [ ] Criacao e edicao de projeto.
- [ ] Geracao de chave unica de integracao por projeto.
- [ ] Exibir snippet basico de embed do widget por projeto.

Criterio de aceite:
- [ ] Projeto criado aparece na lista e possui chave valida.

## Fase 4 - Gestao de pesquisas
- [ ] CRUD de pesquisas por projeto.
- [ ] Configuracao de gatilhos: on_load, after_completed_video, before_cancel.
- [ ] Definir status da pesquisa (rascunho/publicada).

Criterio de aceite:
- [ ] Pesquisa publicada pode ser carregada pela API do widget.

## Fase 5 - Editor de perguntas e regras
- [ ] CRUD de perguntas por pesquisa.
- [ ] Suporte a tipos: nota 0-10, estrelas 0-5, texto, select, checkbox, radio.
- [ ] Campo obrigatorio por pergunta.
- [ ] Regras condicionais (ex.: mostrar pergunta se nota < 5).
- [ ] Ordenacao de perguntas.

Criterio de aceite:
- [ ] Formulario respeita obrigatoriedade e regras condicionais.

## Fase 6 - API publica do widget
- [ ] GET configuracao da pesquisa por chave+gatilho.
- [ ] POST submissao de respostas.
- [ ] Validacao server-side (tipos, required, regras basicas).
- [ ] Captura de metadados: URL, gatilho, user/session id quando houver.

Criterio de aceite:
- [ ] API retorna schema e persiste resposta valida com metadados.

## Fase 7 - Widget embed com VanJS
- [ ] Loader JS embutivel por script externo.
- [ ] Renderizacao dinamica do formulario via schema.
- [ ] Aplicacao de logica condicional no cliente.
- [ ] Abertura em modal (a11y-dialog/VenoBox conforme padrao do projeto).
- [ ] Tratamento de sucesso/erro com feedback visual.

Criterio de aceite:
- [ ] Widget roda em pagina externa e salva respostas no backend.

## Fase 8 - Home showcase
- [ ] Criar hero e secoes de vitrine com classes Squeleton.
- [ ] Integrar Wow2 para animacoes de entrada.
- [ ] Integrar Embla Carousel para demonstracao de pesquisas.
- [ ] Exibir contadores reais com Counter-Up2.
- [ ] Integrar YouTube ID DMrJUyg7TYM e disparar gatilho after_completed_video no fim.
- [ ] Adicionar botoes para simular gatilhos manuais.

Criterio de aceite:
- [ ] Finalizacao do video abre pesquisa com gatilho after_completed_video.

## Fase 9 - Dashboard e analytics
- [ ] Cards de metricas (total respostas, media NPS, taxa conclusao).
- [ ] Lista de respostas recentes com filtros basicos.
- [ ] Animacao dos numeros com Counter-Up2.
- [ ] Uso consistente de icones iccon-* validos.

Criterio de aceite:
- [ ] Dashboard reflete dados reais do SQLite com atualizacao por HTMX.

## Fase 10 - Qualidade e prontidao de producao
- [ ] Hardening de seguranca (session config, headers, validacoes, CSRF no admin).
- [ ] Tratamento global de erros e pagina de fallback.
- [ ] Revisao de UX (loading states, mensagens de erro, acessibilidade).
- [ ] Documentar deploy e operacao.
- [ ] Smoke tests manuais dos fluxos criticos.

Criterio de aceite:
- [ ] Checklist de release aprovado sem bloqueios criticos.

## Backlog tecnico (transversal)
- [ ] Definir calculo oficial de NPS (promotores 9-10, neutros 7-8, detratores 0-6).
- [ ] Padronizar formato de resposta da API (success, data, errors).
- [ ] Definir estrategia de versionamento do widget.
- [ ] Registrar eventos de auditoria no admin (opcional, recomendado).

## Ordem de execucao recomendada
1. Fase 0 e Fase 1
2. Fase 2, Fase 3 e Fase 4
3. Fase 5 e Fase 6
4. Fase 7
5. Fase 8 e Fase 9
6. Fase 10

## Definicao de pronto (DoD)
- [ ] Funcionalidade entregue com validacao no backend.
- [ ] Fluxo HTMX sem refresh indevido.
- [ ] UI consistente com Squeleton.dev.
- [ ] Erros tratados com feedback claro.
- [ ] Testada manualmente no fluxo principal.
