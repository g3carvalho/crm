# LP /fachada-de-acm · Agilize Comunicação Visual

Briefing: `[Agilize Gráfica] LP de Fachada de ACM` (RPS 13124609248 / 12879746264), 24/09/2026.

## O que mudou em relação ao briefing (e por quê)

| Briefing dizia | Realidade encontrada no build de 04/08 | O que foi feito |
|---|---|---|
| React + Vite SPA, react-router | **TanStack Start pré-renderizado**: cada rota é um `index.html` próprio | LP gerada como página pré-renderizada própria |
| Importar componentes da home | Código-fonte não está em nenhum repositório acessível (só o zip de build no Drive) | Script `gerador/build_lp.py` **extrai** header, faixa de marcas, avaliações e footer do `index.html` da home e reusa o CSS compilado. Nada copiado à mão |
| Hostinger reescreve tudo pro SPA | `.htaccess` devolve **404 real** pra rota sem `index.html` | A LP entra como `/fachada-de-acm/index.html`, que o `.htaccess` atual já serve (regra 3). Nenhuma mudança no `.htaccess` |
| Criar `sitemap.xml` e `robots.txt` (não existem) | **Já existem** | `sitemap.xml` atualizado com a LP. `robots.txt` já aponta o sitemap, sem mudança |
| Consentimento em `localStorage.consent_v1` | Chave real é `agl_consent_v1` | LP usa a mesma chave: quem aceitou na home não vê o aviso de novo |
| Campos `hp` (honeypot) e `consent_lgpd` | O formulário real não tem honeypot; payload é `nome, whatsapp, servico, mensagem, pagina, source, utm_*, gclid...` | Payload idêntico ao da home |
| Hero 1920 e 960 | Fotos do cliente têm no máximo 1600px | Hero em 1600 e 960 |

## Rastreamento (réplica exata do bundle da home)

| Item | Na LP |
|---|---|
| Consent Mode v2 default denied + GTM `GTM-W7PBWJ7Z` | Mesmo trecho da home, na mesma ordem (consent antes do GTM) |
| Snippet do CRM | Mesmo, com `data-form="#form-lead"` e `data-consent="#consent_lgpd"` (ids mantidos) |
| Evento de conversão | `form_submit` com `user_data.phone_number` em E.164, igual à home |
| `form_name` | `modal_acm_hero`, `modal_acm_galeria`, `modal_acm_preco`, `modal_acm_tipos`, `modal_acm_cta_final`, `modal_acm_navbar`, `modal_acm_footer`, `modal_acm_clientes` |
| Serviço enviado | Sempre `ACM` (chip fixo no modal, igual a quando a home abre o modal por um card de serviço) |
| page_view | Carga normal de página (não é navegação SPA), o GTM dispara sozinho |

**Atenção Bruno:** se o gatilho da conversão no GTM filtrar `form_name` por lista ou prefixo (`modal_orcamento`, `modal_servicos`...), incluir os nomes `modal_acm_*` acima.

## Teste feito (Chromium, site completo servido localmente)

| Verificação | Desktop 1440 | Celular 390 |
|---|---|---|
| Rolagem horizontal | não | não |
| Maior elemento na tela (LCP) | foto do topo 1600 | foto do topo 960 |
| Peso total rolando a página inteira (com vídeo) | 1,3 MB | 1,45 MB |
| WhatsApp inválido mostra erro | ok | ok |
| `form_submit` + `user_data.phone_number` no dataLayer | ok | ok |
| POST no Apps Script com `servico=ACM`, `gclid` e `utm_source` da URL | ok | ok |
| Lightbox, setas do teclado, FAQ, vídeo tocando ao aparecer | ok | ok |
| Classes usadas existem no CSS da home | 3 faltavam, trocadas por CSS próprio |

Não testado aqui (precisa do site no ar): envio real pra planilha, Tag Assistant, Lighthouse no domínio.

## Pendências com o Luciano (a página já está pronta sem elas)

| Item | Hoje na página | Onde mudar |
|---|---|---|
| Preço por m² | Coluna "Vistoria grátis" (sem número) | `gerador/config.py` → `PRECO_M2` |
| Prazo médio em dias | "Prazo: na vistoria" | `PRAZO_DIAS` |
| Resposta em até 24h | "Sem custo / resposta rápida"; números mostram "MS" no lugar de "24h"; description sem 24h | `RESPOSTA_24H = True` |
| Projeto em 3D | "Projeto sob medida" | `PROJETO_3D = True` |
| Garantia | FAQ fala só da durabilidade do material | `GARANTIA = "X anos"` |
| Nome do cliente do painel com mapa em LED | legenda "Painel com LED" | `GALERIA` em `build_lp.py` |
| Confirmar que Romero, Lopes & Rabelo e Franco's Café são ACM | incluídas | ver `ACM-FOTOS.md` |

Depois de mudar: `python3 gerador/build_lp.py <index.html da home> ` e subir de novo só `fachada-de-acm/index.html`.

## Deploy (Bruno, Hostinger)

1. Copiar o conteúdo de `deploy/` pra raiz do `public_html`, **sem apagar nada**:
   - `fachada-de-acm/index.html` (nova)
   - `img/acm/` (nova, 3 MB, 29 arquivos)
   - `og-fachada-acm.jpg` (nova)
   - `sitemap.xml` (substitui: só ganhou a linha da LP)
2. Abrir `https://agilizegrafica.com.br/fachada-de-acm` direto e dar F5 (tem que responder 200, não 404).
3. Gê: enviar o sitemap no Search Console e pedir indexação da URL.

**Se um dia a home for rebuildada e o CSS mudar de hash** (`/assets/styles-XXXX.css`), a LP perde o estilo. Nesse caso rodar o gerador de novo com o `index.html` novo da home. O script para com erro se não achar algum pedaço da home.

## Fica pro Bruno (precisa do código-fonte da home)

1. Card "ACM" da seção de serviços da home → link pra `/fachada-de-acm`.
2. Footer da home → link "Fachada em ACM" em Navegação (na LP já existe).
3. Opcional, quando o fonte aparecer: migrar a LP pra uma rota TanStack de verdade (`src/routes/fachada-de-acm.tsx`) reaproveitando este HTML como referência. Até lá o HTML estático funciona e é mais leve.

Não dá pra fazer 1 e 2 editando o HTML pré-renderizado: a home hidrata com React e o bundle redesenha a seção sem o link.

## Checklist de aceite

- [ ] Abre por acesso direto e F5 em `/fachada-de-acm`
- [x] Visual igual à home (mesmo CSS, fontes, botões, espaçamentos)
- [x] 12 fotos de ACM + vídeo na galeria, WebP, com alt
- [ ] Lead de teste cai na planilha com `servico = ACM`
- [ ] Conversão "Enviar formulário de lead" dispara no Tag Assistant
- [x] Título, description, canonical, OG e JSON-LD (Service + FAQPage + Breadcrumb)
- [x] sitemap.xml atualizado (robots.txt já apontava)
- [ ] Link da home e do footer da home (depende do fonte)
- [ ] Preço, prazo e garantia confirmados com o Luciano
- [ ] LCP celular abaixo de 2,5 s (medir no ar)

## Arquivos

```
deploy/            → vai pro public_html
gerador/build_lp.py → gera a página a partir do index.html da home
gerador/config.py   → itens a confirmar com o cliente
ACM-FOTOS.md        → original do Drive → nome novo
```
