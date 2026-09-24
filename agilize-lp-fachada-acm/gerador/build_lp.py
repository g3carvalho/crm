#!/usr/bin/env python3
"""Gera /fachada-de-acm/index.html (página pré-renderizada, sem hidratação).

Por que estático: o site é TanStack Start pré-renderizado e o código-fonte não está
disponível. Sem o fonte não dá pra registrar rota nova no bundle, e o .htaccess devolve
404 real pra qualquer rota sem index.html. Então a LP é um HTML próprio que:
  - reaproveita o CSS compilado da home (mesmas classes Tailwind, mesmo visual);
  - extrai header, faixa de marcas, avaliações e footer direto do index.html da home
    (nada digitado à mão: se a home mudar, basta rodar de novo);
  - replica 1:1 a lógica do modal de orçamento do bundle (evento form_submit com
    user_data.phone_number, payload do Apps Script, id #form-lead pro snippet do CRM,
    consentimento agl_consent_v1, atribuição agl_attr).

Uso:
    python3 gerador/build_lp.py caminho/para/home/index.html [pasta-de-saida]
Saída padrão: deploy/fachada-de-acm/index.html
"""
import html
import json
import re
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import config as C  # noqa: E402

ROOT = Path(__file__).resolve().parent.parent
SITE = "https://agilizegrafica.com.br"
URL = f"{SITE}/fachada-de-acm"
IMG = "/img/acm"

# ---------------------------------------------------------------- home
if len(sys.argv) < 2:
    sys.exit("uso: build_lp.py <home index.html> [saida]")
HOME = Path(sys.argv[1]).read_text(encoding="utf-8")
OUT_DIR = Path(sys.argv[2]) if len(sys.argv) > 2 else ROOT / "deploy"


def grab(pattern, src=HOME, flags=re.S):
    m = re.search(pattern, src, flags)
    if not m:
        sys.exit(f"não achei na home: {pattern[:80]}")
    return m.group(0)


def unhide(s):
    """Remove o estado inicial das animações (framer-motion) que deixa tudo invisível sem JS."""
    return re.sub(r'\sstyle="opacity:0;transform:[^"]*"', "", s)


def icon(name, cls):
    """Ícone lucide extraído da home, com a classe trocada."""
    svg = grab(r'<svg[^>]*lucide-' + name + r'\b.*?</svg>')
    return re.sub(r'class="lucide [^"]*"', f'class="lucide lucide-{name} {cls}"', svg, count=1)


CSS_HREF = re.search(r'href="(/assets/styles-[^"]+\.css)"', HOME).group(1)
LOGO = re.search(r'src="(/assets/logo-agilize-branco-[^"]+\.webp)"', HOME).group(1)
WA_SVG = grab(r'<svg viewBox="0 0 32 32".*?</svg>')
WA = re.sub(r'class="[^"]*"', 'class="h-[1.2em] w-[1.2em] shrink-0"', WA_SVG, count=1)
HEAD_SNIPPETS = grab(r"<script>window\.dataLayer=window\.dataLayer.*?data-consent=\"#consent_lgpd\"></script>")
GTM_NOSCRIPT = grab(r"<noscript><iframe src=\"https://www\.googletagmanager\.com/ns\.html.*?</noscript>")
FAVICONS = "".join(re.findall(r'<link rel="(?:icon|apple-touch-icon)"[^>]*/>', HOME))

# header: âncoras voltam pra home, botão abre o modal com ACM
header = grab(r"<header\b.*?</header>")
header = header.replace('href="#inicio"', 'href="/"')
header = re.sub(r'href="#(servicos|projetos|clientes|contato)"', r'href="/#\1"', header)
header = header.replace('<button type="button" class="hidden items-center',
                        '<button type="button" data-lead="modal_acm_navbar" class="hidden items-center', 1)
header = header.replace('<button aria-label="Abrir menu"',
                        '<button type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-mobile" data-menu', 1)
nav_links = re.findall(r'<a href="/#(\w+)"[^>]*>(<svg.*?</svg>)([^<]+)</a>', header, re.S)
menu_mobile = (
    '<div id="menu-mobile" hidden class="border-t border-white/5 bg-[#050505]/95 backdrop-blur-xl lg:hidden">'
    '<div class="mx-auto flex max-w-7xl flex-col gap-1 px-5 py-4">'
    + "".join(
        f'<a href="/#{a}" class="flex items-center gap-2.5 rounded-lg px-3 py-3 text-lg font-medium text-white/80 '
        f'transition-colors hover:bg-white/5 hover:text-[#b8ff00]">'
        + re.sub(r'class="lucide ([\w-]+) [^"]*"', r'class="lucide \1 h-5 w-5 text-[#b8ff00]"', svg)
        + f"{label}</a>"
        for a, svg, label in nav_links
    )
    + '<button type="button" data-lead="modal_acm_navbar_mobile" class="mt-2 inline-flex items-center justify-center gap-2 '
    'rounded-full bg-[#b8ff00] px-5 py-3 text-sm font-bold uppercase tracking-wider text-[#050505]">'
    + re.sub(r'class="[^"]*"', 'class="h-4 w-4 shrink-0"', WA_SVG, count=1)
    + "Solicitar Orçamento</button></div></div>"
)
header = header.replace("</header>", menu_mobile + "</header>")

# faixa de marcas: sem alteração (só tira o estado invisível da animação)
clientes = unhide(grab(r'<section id="clientes".*?</section>'))
clientes = clientes.replace('<button type="button" class="inline-flex', '<button type="button" data-lead="modal_acm_clientes" class="inline-flex')
if re.search(r'<button(?![^>]*data-lead)', clientes):
    sys.exit('botão sem ação na faixa de marcas da home: revisar build_lp.py')

# avaliações: os 3 cards pedidos no briefing
REVIEWERS = ["Abimael Mendes", "Ullia Taketa", "Nayanne Chaves"]
figs = re.findall(r"<figure\b.*?</figure>", HOME, re.S)
reviews = []
for name in REVIEWERS:
    f = next((x for x in figs if f">{name}<" in x), None)
    if not f:
        sys.exit(f"avaliação não encontrada na home: {name}")
    reviews.append(f.replace("w-[300px] shrink-0", "w-full").replace(" sm:w-[350px]", ""))

# footer: âncoras voltam pra home, botão abre modal, link da LP em Navegação
footer = unhide(grab(r'<footer id="contato".*?</footer>'))
footer = footer.replace('href="#inicio"', 'href="/"')
footer = re.sub(r'href="#(servicos|projetos|clientes|contato)"', r'href="/#\1"', footer)
footer = footer.replace('<button type="button" class="inline-flex', '<button type="button" data-lead="modal_acm_footer" class="inline-flex', 1)
footer = footer.replace(
    '<li><a href="/#servicos" class="transition-colors hover:text-[#b8ff00]">Serviços</a></li>',
    '<li><a href="/#servicos" class="transition-colors hover:text-[#b8ff00]">Serviços</a></li>'
    '<li><a href="/fachada-de-acm" aria-current="page" class="text-[#b8ff00] transition-colors hover:text-[#b8ff00]">Fachada em ACM</a></li>',
)

# ---------------------------------------------------------------- blocos comuns
BTN = ("inline-flex items-center justify-center gap-2 rounded-full font-bold uppercase tracking-wider "
       "transition-all duration-300 px-8 py-4 text-base bg-[#b8ff00] text-[#050505] hover:bg-[#9ce000] "
       "hover:-translate-y-0.5 hover:shadow-[0_20px_40px_-12px_rgba(184,255,0,0.45)]")
EYEBROW_CLS = ("inline-flex items-center gap-2 rounded-full border border-[#b8ff00]/30 bg-[#b8ff00]/5 px-4 py-1.5 "
               "text-xs font-semibold uppercase tracking-[0.2em] text-[#b8ff00]")
H2 = "mt-5 font-impact text-4xl font-normal uppercase leading-[0.95] tracking-tight text-white sm:text-5xl"
ARROW = icon("arrow-right", "h-4 w-4")
WA_SMALL = WA.replace('class="h-[1.2em] w-[1.2em] shrink-0"', 'class="h-3.5 w-3.5"')
CHECK = icon("check", "h-3.5 w-3.5 text-[#b8ff00]")


def eyebrow(t):
    return f'<span class="{EYEBROW_CLS}"><span class="h-1.5 w-1.5 rounded-full bg-[#b8ff00]"></span> {t}</span>'


def h2(t, accent=""):
    acc = f' <span class="text-[#b8ff00]">{accent}</span>' if accent else ""
    return f'<h2 class="{H2}">{t}{acc}</h2>'


def cta(label, form, extra=""):
    return f'<button type="button" data-lead="{form}" class="{BTN} {extra}">{WA}{label} {ARROW}</button>'


def svg(paths, cls="h-6 w-6"):
    return (f'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" '
            f'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{cls}" '
            f'aria-hidden="true">{paths}</svg>')


# lucide (mesmo set da home: linha fina)
I_SHIELD = svg('<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>')
I_RULER = svg('<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>')
I_PALETTE = svg('<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>')
I_BULB = svg('<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>')
I_CLIP = svg('<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>')
I_PEN = svg('<path d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z"/><path d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18"/><path d="m2.3 2.3 7.286 7.286"/><circle cx="11" cy="11" r="2"/>')
I_FACTORY = svg('<path d="M12 16h.01"/><path d="M16 16h.01"/><path d="M3 19a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.5a.5.5 0 0 0-.769-.422l-4.462 2.844A.5.5 0 0 1 15 10.5v-2a.5.5 0 0 0-.769-.422L9.77 10.922A.5.5 0 0 1 9 10.5V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M8 16h.01"/>')
I_WRENCH = svg('<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>')
I_AWARD = svg('<path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/><circle cx="12" cy="8" r="6"/>', "h-4 w-4")
I_CLIP_S = I_CLIP.replace('class="h-6 w-6"', 'class="h-4 w-4"')
I_FACTORY_S = I_FACTORY.replace('class="h-6 w-6"', 'class="h-4 w-4"')
I_IMAGES = icon("images", "h-4 w-4")
I_PLUS = icon("plus", "h-4 w-4 shrink-0 text-[#b8ff00]")
I_X = icon("x", "h-5 w-5")
I_CHEV = icon("chevron-down", "h-5 w-5 shrink-0 text-[#b8ff00] transition-transform duration-300")
I_ROCKET = icon("rocket", "h-5 w-5")
I_PREV = svg('<path d="m15 18-6-6 6-6"/>', "h-6 w-6")
I_NEXT = svg('<path d="m9 18 6-6-6-6"/>', "h-6 w-6")

# ---------------------------------------------------------------- conteúdo
# (arquivo, legenda, alt, largura, altura): ver ACM-FOTOS.md
GALERIA = [
    ("acm-hero-1600", "Lavey Lavanderia Express", "Fachada em ACM branco com letra caixa vermelha da Lavey Lavanderia Express em Campo Grande/MS", 1599, 899, "acm-hero-960"),
    ("acm-blessed-things-1", "Blessed Things", "Fachada em ACM cinza com letra caixa dourada da loja Blessed Things em Campo Grande/MS", 1600, 900, None),
    ("acm-romero-lopes-rabelo-1", "Romero, Lopes & Rabelo", "Painel de fachada em ACM preto do escritório Romero, Lopes & Rabelo em Campo Grande/MS", 1200, 1600, None),
    ("acm-lavey-lavanderia-2", "Lavey Lavanderia Express", "Fachada em ACM da Lavey Lavanderia Express vista de lado, com letra caixa em relevo", 1600, 1013, None),
    ("acm-francos-cafe-1", "Franco's Café", "Letreiro luminoso da Franco's Café com letra caixa iluminada sobre fachada em ACM preto", 900, 1600, None),
    ("acm-blessed-things-2", "Blessed Things", "Marquise revestida em ACM cinza da loja Blessed Things em Campo Grande/MS", 1600, 900, None),
    ("acm-moria-motos-1", "Moriá Motos", "Fachada em ACM com comunicação visual da oficina Moriá Motos em Campo Grande/MS", 1599, 1186, None),
    ("acm-lavey-totem-1", "Lavey Lavanderia Express", "Totem em ACM laranja da Lavey Lavanderia Express, parte do projeto de fachada em ACM", 899, 1599, None),
    ("acm-painel-led-mapa-1", "Painel com LED", "Painel recortado com iluminação em LED na parede, feito pela Agilize em Campo Grande/MS", 1600, 900, None),
    ("acm-blessed-things-3", "Blessed Things", "Caixa da fachada em ACM da Blessed Things vista de baixo, com acabamento reto nas quinas", 900, 1600, None),
    ("acm-moria-motos-2", "Moriá Motos", "Fachada em ACM da Moriá Motos vista da calçada em Campo Grande/MS", 1186, 1599, None),
    ("acm-blessed-things-4", "Blessed Things", "Forro da marquise em ACM com spots embutidos na loja Blessed Things", 900, 1600, None),
]

TIPOS = [
    ("acm-blessed-things-3", "ACM liso", "Revestimento limpo que renova a fachada inteira."),
    ("acm-lavey-lavanderia-2", "ACM com letra caixa", "Letras em relevo que dão peso à sua marca."),
    ("acm-painel-led-mapa-1", "ACM com iluminação em LED", "Luz que destaca a fachada quando escurece."),
    ("acm-francos-cafe-1", "ACM com letreiro luminoso", "Sua marca acesa e visível de longe."),
]

FAQ = [
    ("O que é ACM?", "Painel de alumínio composto: duas chapas de alumínio com núcleo de polietileno. Leve, rígido e resistente ao tempo."),
    ("ACM dura quanto tempo?", "Com instalação correta, mais de 10 anos sem perder cor ou acabamento."
     + (f" A Agilize dá {C.GARANTIA} de garantia." if C.GARANTIA else "")),
    ("Dá pra colocar letra caixa e LED em cima do ACM?", "Sim. É a combinação mais pedida: painel liso com letras em relevo iluminadas."),
    ("Vocês atendem fora de Campo Grande?", "Sim, todo o Mato Grosso do Sul."),
    ("Quanto tempo leva?", "Depende do tamanho. Após a vistoria informamos o prazo exato de fabricação e instalação."),
]

TITLE = "Fachada em ACM em Campo Grande MS | Agilize Comunicação Visual"
DESC = ("Fachada em ACM com projeto, fabricação própria e instalação em Campo Grande e todo o MS. "
        + ("Vistoria grátis e orçamento em até 24h. " if C.RESPOSTA_24H else "Vistoria grátis e orçamento sem compromisso. ")
        + "Mais de 15 anos de experiência.")

# ---------------------------------------------------------------- seções
S_HERO = f"""
<section id="inicio" class="acm-hero relative isolate overflow-hidden">
  <picture>
    <source media="(max-width: 767px)" srcset="{IMG}/acm-hero-960.webp" />
    <img src="{IMG}/acm-hero-1600.webp" alt="Fachada em ACM branco com letra caixa vermelha da Lavey Lavanderia Express, feita pela Agilize em Campo Grande/MS" width="1599" height="899" fetchpriority="high" decoding="async" class="acm-hero-img absolute inset-0 -z-10 h-full w-full object-cover" />
  </picture>
  <div aria-hidden="true" class="acm-hero-overlay absolute inset-0 -z-10"></div>
  <div class="mx-auto flex max-w-[86rem] flex-col items-center px-5 text-center sm:px-8 lg:items-start lg:text-left">
    {eyebrow("Fachada em ACM · Campo Grande/MS")}
    <h1 class="mt-6 max-w-4xl font-impact text-4xl font-normal uppercase leading-[0.95] tracking-tight text-white sm:text-5xl md:text-6xl lg:text-7xl">Fachada em ACM que <span class="text-[#b8ff00]">valoriza sua empresa.</span></h1>
    <p class="mt-6 max-w-xl text-base leading-relaxed text-white/80 sm:text-lg">Projeto, fabricação própria e instalação em Campo Grande e todo o MS.</p>
    <div class="acm-ctas mt-9 flex flex-col items-center gap-3 sm:flex-row sm:flex-wrap">
      {cta("Quero orçamento de fachada em ACM", "modal_acm_hero", "acm-btn-block")}
      <a href="#galeria" class="acm-btn-ghost acm-btn-block">{I_IMAGES}Ver fachadas em ACM</a>
    </div>
    <ul class="acm-chips mt-8">
      <li>{I_CLIP_S}Vistoria grátis</li>
      <li>{I_FACTORY_S}Fabricação própria</li>
      <li>{I_AWARD}Mais de 15 anos</li>
    </ul>
  </div>
</section>"""

numeros = [
    ("15+", "anos em Campo Grande"),
    ("Centenas", "de fachadas entregues"),
    ("100%", "fabricação própria"),
    ("24h", "resposta ao orçamento") if C.RESPOSTA_24H else ("MS", "atendimento em todo o estado"),
]
S_NUMEROS = f"""
<section aria-label="Agilize em números" class="relative border-y border-white/5 bg-[#0a0a0a] py-12 sm:py-16">
  <dl class="acm-stats mx-auto max-w-7xl px-5 sm:px-8">
    {"".join(f'<div><dt class="acm-stat-label">{l}</dt><dd class="acm-stat-num font-impact">{n}</dd></div>' for n, l in numeros)}
  </dl>
</section>"""


def galeria_item(i, g):
    arq, leg, alt, w, h, small = g
    small = small or f"{arq}-720"
    return (
        f'<li class="acm-masonry-item"><button type="button" data-lb="{i}" '
        f'class="group relative block w-full overflow-hidden rounded-2xl border border-white/10" aria-label="Ampliar foto: {html.escape(leg)}">'
        f'<img src="{IMG}/{small}.webp" data-full="{IMG}/{arq}.webp" alt="{html.escape(alt)}" width="{w}" height="{h}" '
        f'loading="lazy" decoding="async" class="block h-auto w-full transition-transform duration-700 ease-out group-hover:scale-110" />'
        f'<span class="acm-cap absolute inset-x-0 bottom-0 flex items-center justify-between p-4">'
        f'<span class="line-clamp-1 text-xs font-medium text-white">{html.escape(leg)}</span>{I_PLUS}</span></button></li>'
    )


REEL = (
    '<li class="acm-masonry-item"><div class="relative overflow-hidden rounded-2xl border border-white/10">'
    f'<video class="acm-reel block h-auto w-full" muted loop playsinline preload="none" width="474" height="850" '
    f'poster="{IMG}/acm-blessed-things-reel-poster.webp" aria-label="Vídeo da fachada em ACM da Blessed Things">'
    f'<source src="{IMG}/acm-blessed-things-reel.webm" type="video/webm" />'
    f'<source src="{IMG}/acm-blessed-things-reel.mp4" type="video/mp4" /></video>'
    '<span class="acm-cap acm-cap-on absolute inset-x-0 bottom-0 flex items-center justify-between p-4">'
    '<span class="line-clamp-1 text-xs font-medium text-white">Blessed Things · vídeo</span></span></div></li>'
)

S_GALERIA = f"""
<section id="galeria" class="relative py-20 sm:py-28">
  <div class="mx-auto max-w-7xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Fachadas em ACM")}
      {h2("Veja o ACM instalado em", "Campo Grande.")}
      <p class="mt-4 text-white/60">Obras reais da Agilize. Toque na foto para ampliar.</p>
    </div>
    <ul class="acm-masonry mt-12">
      {REEL}
      {"".join(galeria_item(i, g) for i, g in enumerate(GALERIA))}
    </ul>
    <div class="mt-12 flex justify-center">{cta("Quero uma fachada assim", "modal_acm_galeria", "acm-btn-block")}</div>
  </div>
</section>"""

vantagens = [
    (I_SHIELD, "Resiste a sol e chuva", "Não desbota, não enferruja, não empena."),
    (I_RULER, "Acabamento reto e limpo", "Painéis alinhados, sem emenda aparente."),
    (I_PALETTE, "Várias cores e texturas", "Branco, preto, escovado, madeirado e mais."),
    (I_BULB, "Combina com letra caixa e LED", "Fachada que aparece de dia e de noite."),
]
CARD = "flex h-full flex-col gap-5 rounded-2xl border border-white/10 bg-[#101010] p-6 sm:p-7"
ICO = "grid h-12 w-12 place-items-center rounded-xl bg-[#b8ff00]/10 text-[#b8ff00]"
S_PORQUE = f"""
<section class="relative border-y border-white/5 bg-[#0a0a0a] py-20 sm:py-28">
  <div class="mx-auto max-w-7xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Por que ACM")}
      {h2("O revestimento que dura e", "não sai de moda.")}
    </div>
    <ul class="acm-grid-4 mt-12">
      {"".join(f'<li class="{CARD}"><span class="{ICO}">{ic}</span><div><h3 class="text-lg font-bold text-white">{t}</h3><p class="mt-2 text-sm leading-relaxed text-white/60">{d}</p></div></li>' for ic, t, d in vantagens)}
    </ul>
  </div>
</section>"""

col_preco = (("A partir de", f"{C.PRECO_M2}/m²", "instalado, com estrutura") if C.PRECO_M2
             else ("Vistoria", "Grátis", "medição no local, sem compromisso"))
col_prazo = (("Prazo médio", f"{C.PRAZO_DIAS} dias", "da aprovação à instalação") if C.PRAZO_DIAS
             else ("Prazo", "Na vistoria", "você sai sabendo a data da instalação"))
col_orc = (("Orçamento", "Até 24h", "vistoria sem compromisso") if C.RESPOSTA_24H
           else ("Orçamento", "Sem custo", "resposta rápida pelo WhatsApp"))
S_PRECO = f"""
<section id="preco" class="relative py-20 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Preço e prazo")}
      {h2("Quanto custa uma fachada", "em ACM?")}
    </div>
    <ul class="acm-price mt-12">
      {"".join(f'<li><p class="acm-price-k">{k}</p><p class="acm-price-v font-impact">{v}</p><p class="acm-price-s">{s}</p></li>' for k, v, s in (col_preco, col_prazo, col_orc))}
    </ul>
    <p class="mx-auto mt-6 max-w-2xl text-center text-sm text-white/50">Valor final depende de medidas, cor do ACM e iluminação. A vistoria define tudo.</p>
    <div class="mt-10 flex justify-center">{cta("Pedir meu orçamento", "modal_acm_preco", "acm-btn-block")}</div>
  </div>
</section>"""

S_TIPOS = f"""
<section class="relative border-y border-white/5 bg-[#0a0a0a] py-20 sm:py-28">
  <div class="mx-auto max-w-7xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Opções")}
      {h2("ACM do jeito que sua", "marca pede.")}
    </div>
    <ul class="acm-grid-4 mt-12">
      {"".join(f'<li><button type="button" data-lead="modal_acm_tipos" class="group block w-full overflow-hidden rounded-2xl border border-white/10 bg-[#101010] text-left transition-all duration-500 hover:-translate-y-1 hover:border-[#b8ff00]/40"><span class="acm-tipo-img block overflow-hidden"><img src="{IMG}/{a}-720.webp" alt="{html.escape(t)}: exemplo de fachada em ACM feita pela Agilize" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110" /></span><span class="block p-5"><span class="block text-lg font-bold text-white">{t}</span><span class="mt-1 block text-sm text-white/60">{d}</span><span class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-[#b8ff00]">{WA_SMALL} Orçar</span></span></button></li>' for a, t, d in TIPOS)}
    </ul>
  </div>
</section>"""

passos = [
    (I_CLIP, "01", "Vistoria grátis"),
    (I_PEN, "02", "Projeto 3D" if C.PROJETO_3D else "Projeto sob medida"),
    (I_FACTORY, "03", "Fabricação própria"),
    (I_WRENCH, "04", "Instalação"),
]
S_PROCESSO = f"""
<section class="relative py-20 sm:py-28">
  <div class="mx-auto max-w-7xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Como funciona")}
      {h2("Da vistoria à instalação,", "tudo com a Agilize.")}
    </div>
    <ol class="acm-steps mt-12">
      {"".join(f'<li class="acm-step"><span class="{ICO}">{ic}</span><span class="acm-step-n font-impact">{n}</span><span class="text-lg font-bold text-white">{t}</span></li>' for ic, n, t in passos)}
    </ol>
  </div>
</section>"""

S_AVALIACOES = f"""
<section class="relative py-20 sm:py-28">
  <div class="mx-auto max-w-7xl px-5 sm:px-8">
    <div class="mx-auto max-w-3xl text-center">
      {eyebrow("Avaliações no Google")}
      {h2("O que diz quem já transformou a", "fachada.")}
      <p class="mt-4 text-white/60">Avaliações reais de clientes da Agilize no Google.</p>
    </div>
    <div class="acm-reviews mt-12">{"".join(reviews)}</div>
  </div>
</section>"""


def faq_item(i, q, a):
    aberto = i == 0
    return (
        f'<div class="acm-faq overflow-hidden rounded-2xl border transition-colors '
        f'{"border-[#b8ff00]/30 bg-[#101010]" if aberto else "border-white/10 bg-[#0a0a0a]"}">'
        f'<h3><button type="button" id="faq-q{i}" aria-controls="faq-a{i}" aria-expanded="{"true" if aberto else "false"}" '
        f'class="flex w-full items-center justify-between gap-4 p-5 text-left">'
        f'<span class="min-w-0 pr-2 text-base font-semibold text-white sm:text-lg">{q}</span>'
        + I_CHEV.replace("transition-transform duration-300", "transition-transform duration-300" + (" rotate-180" if aberto else ""))
        + f'</button></h3><div id="faq-a{i}" role="region" aria-labelledby="faq-q{i}" class="grid transition-all duration-500 '
        f'{"grid-rows-[1fr] opacity-100" if aberto else "grid-rows-[0fr] opacity-0"}"><div class="overflow-hidden">'
        f'<p class="px-5 pb-5 text-white/70">{a}</p></div></div></div>'
    )


S_FAQ = f"""
<section class="relative border-y border-white/5 bg-[#0a0a0a] py-20 sm:py-28">
  <div class="mx-auto max-w-4xl px-5 sm:px-8">
    <div class="text-center">
      {eyebrow("FAQ")}
      {h2("Dúvidas sobre", "ACM")}
    </div>
    <div class="mt-12 space-y-3">{"".join(faq_item(i, q, a) for i, (q, a) in enumerate(FAQ))}</div>
  </div>
</section>"""

S_CTA = f"""
<section class="relative overflow-hidden py-24 sm:py-32">
  <img src="{IMG}/acm-blessed-things-1-720.webp" srcset="{IMG}/acm-blessed-things-1-720.webp 720w, {IMG}/acm-blessed-things-1.webp 1600w" sizes="100vw" alt="" aria-hidden="true" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover" />
  <div class="absolute inset-0 bg-[#050505]/85"></div>
  <div aria-hidden="true" class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-[#050505] to-transparent"></div>
  <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-[#050505] to-transparent"></div>
  <div class="relative mx-auto max-w-4xl px-5 text-center sm:px-8">
    {eyebrow("Chegou a hora")}
    <h2 class="mt-6 font-impact text-4xl font-normal uppercase leading-[0.95] tracking-tight text-white sm:text-5xl lg:text-7xl">Sua fachada em ACM começa com uma <span class="text-[#b8ff00]">vistoria grátis.</span></h2>
    <div class="mt-10"><button type="button" data-lead="modal_acm_cta_final" class="{BTN} acm-btn-block text-base sm:text-lg">{WA}{I_ROCKET} Quero solicitar meu orçamento agora</button></div>
    <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-white/60 sm:text-sm">
      {"".join(f'<li class="flex items-center gap-1.5">{CHECK} {t}</li>' for t in ("Resposta rápida", "Sem compromisso", "Fabricação própria"))}
    </ul>
  </div>
</section>"""

# ---------------------------------------------------------------- modal (réplica do bundle)
MODAL = f"""
<div id="lead-modal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" data-close></div>
  <div class="absolute inset-0 grid place-items-center p-4 pointer-events-none">
    <div role="dialog" aria-modal="true" aria-labelledby="lead-modal-title" class="acm-dialog relative w-full max-w-md rounded-3xl border border-white/10 bg-[#0d0d0d] p-6 shadow-[0_30px_80px_-20px_rgba(0,0,0,0.9)] sm:p-8">
      <button type="button" aria-label="Fechar" data-close class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-white/5 text-white/70 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#b8ff00]">{icon("x", "h-4 w-4")}</button>
      <div id="lead-ok" class="py-6 text-center" hidden>
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#b8ff00]/15 text-[#b8ff00]">{svg('<path d="M20 6 9 17l-5-5"/>', "h-7 w-7").replace('stroke-width="2"', 'stroke-width="3"')}</div>
        <h2 class="mt-5 font-impact text-2xl uppercase text-white">Tudo certo!</h2>
        <p class="mt-2 text-sm text-white/60">Abrimos o WhatsApp com sua mensagem. Se não abriu, <a id="lead-ok-link" href="#" target="_blank" rel="noopener noreferrer" class="font-semibold text-[#b8ff00] underline">clique aqui</a>.</p>
      </div>
      <div id="lead-body">
        <h2 id="lead-modal-title" class="pr-8 font-impact text-2xl uppercase leading-tight text-white sm:text-3xl">Solicite seu <span class="text-[#b8ff00]">orçamento</span></h2>
        <p class="mt-2 text-sm text-white/60">Preencha rapidinho e a gente já te chama no WhatsApp.</p>
        <form id="form-lead" class="mt-6 space-y-4" novalidate>
          <div><label for="lead-nome" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50">Nome</label><input id="lead-nome" name="nome" type="text" autocomplete="name" required class="w-full rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder-white/30 outline-none transition-colors focus:border-[#b8ff00]/50" placeholder="Seu nome" /></div>
          <div><label for="lead-telefone" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/50">WhatsApp</label><input id="lead-telefone" name="telefone" type="tel" inputmode="numeric" autocomplete="tel" enterkeyhint="send" required aria-invalid="false" aria-describedby="lead-telefone-erro" class="w-full rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-white placeholder-white/30 outline-none transition-colors focus:border-[#b8ff00]/50" placeholder="(67) 99999-9999" /><span id="lead-telefone-erro" role="alert" hidden class="mt-1 block text-xs text-red-400">Informe um WhatsApp válido com DDD.</span></div>
          <div class="rounded-xl border border-[#b8ff00]/30 bg-[#b8ff00]/5 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wider text-white/50">Serviço</p><p class="mt-0.5 font-semibold text-[#b8ff00]">ACM</p><input type="hidden" name="servico" value="ACM" /></div>
          <input type="checkbox" id="consent_lgpd" checked hidden readonly />
          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#b8ff00] px-6 py-3.5 text-sm font-bold uppercase tracking-wider text-[#050505] transition-all hover:bg-[#9ce000] disabled:opacity-60"><span data-label>Quero meu orçamento</span>{icon("arrow-right", "h-4 w-4")}</button>
          <p class="form-consent text-center text-[11px] leading-relaxed text-white/40">Ao enviar os seus dados você está de acordo com os nossos <a href="/termos" target="_blank" rel="noopener" class="underline hover:text-white/70">termos</a> e <a href="/politica-de-privacidade" target="_blank" rel="noopener" class="underline hover:text-white/70">políticas</a>.</p>
        </form>
      </div>
    </div>
  </div>
</div>"""

COOKIES = """
<div id="cookie-banner" role="dialog" aria-label="Aviso de cookies" hidden class="fixed inset-x-3 bottom-24 z-[90] mx-auto max-w-2xl rounded-2xl border border-white/10 bg-[#0d0d0d]/95 p-4 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.9)] backdrop-blur-xl sm:bottom-6 sm:p-5">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-xs leading-relaxed text-white/70 sm:text-sm">Usamos cookies para medir o desempenho dos nossos anúncios e melhorar sua experiência. Você pode aceitar ou recusar. <a href="/politica-de-privacidade" target="_blank" rel="noopener" class="underline hover:text-white">Saiba mais</a>.</p>
    <div class="flex shrink-0 gap-2">
      <button type="button" data-consent="denied" class="rounded-full border border-white/15 px-4 py-2 text-xs font-semibold text-white/80 transition-colors hover:bg-white/5">Recusar</button>
      <button type="button" data-consent="granted" class="rounded-full bg-[#b8ff00] px-5 py-2 text-xs font-bold uppercase tracking-wider text-[#050505] transition-colors hover:bg-[#9ce000]">Aceitar</button>
    </div>
  </div>
</div>"""

LIGHTBOX = f"""
<div id="lightbox" class="acm-lightbox fixed inset-0 hidden" role="dialog" aria-modal="true" aria-label="Foto ampliada">
  <div class="absolute inset-0 bg-black/90" data-lb-close></div>
  <figure class="acm-lb-fig">
    <img id="lb-img" alt="" />
    <figcaption id="lb-cap" class="mt-3 text-center text-sm text-white/70"></figcaption>
  </figure>
  <button type="button" data-lb-close aria-label="Fechar" class="acm-lb-btn" style="top:1rem;right:1rem">{I_X}</button>
  <button type="button" data-lb-prev aria-label="Foto anterior" class="acm-lb-btn" style="left:.75rem;top:50%">{I_PREV}</button>
  <button type="button" data-lb-next aria-label="Próxima foto" class="acm-lb-btn" style="right:.75rem;top:50%">{I_NEXT}</button>
</div>"""

# CSS próprio só pro que a home não tem (grid masonry, hero full-bleed, faixas).
# Tokens iguais aos da home: #050505, #0a0a0a, #101010, #b8ff00.
STYLE = """
:root{--acm-bg:#050505;--acm-bg2:#0a0a0a;--acm-card:#101010;--acm-lime:#b8ff00;--acm-lime-h:#9ce000}
html{scroll-behavior:smooth}
.acm-hero{min-height:88vh;display:flex;align-items:center;padding:7rem 0 5rem}
@media(min-width:1024px){.acm-hero{min-height:92vh;padding:9rem 0 6rem}}
.acm-hero-overlay{background:linear-gradient(to right,rgba(5,5,5,.78),rgba(5,5,5,.6) 55%,rgba(5,5,5,.45)),linear-gradient(to top,#050505 0%,transparent 35%)}
@media(max-width:1023px){.acm-hero-overlay{background:rgba(5,5,5,.66);box-shadow:inset 0 -140px 120px -40px #050505}}
.acm-btn-ghost{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border-radius:9999px;border:1px solid rgba(255,255,255,.25);padding:1rem 2rem;font-weight:700;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;color:#fff;transition:all .3s}
.acm-btn-ghost:hover{border-color:var(--acm-lime);color:var(--acm-lime)}
@media(max-width:639px){.acm-btn-block{width:100%}}
:is(button,a):focus-visible{outline:2px solid var(--acm-lime);outline-offset:3px}
.acm-chips{display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem}
@media(min-width:1024px){.acm-chips{justify-content:flex-start}}
.acm-chips li{display:inline-flex;align-items:center;gap:.4rem;border-radius:9999px;border:1px solid rgba(255,255,255,.12);background:rgba(5,5,5,.55);padding:.45rem .9rem;font-size:.8rem;font-weight:600;color:rgba(255,255,255,.85);backdrop-filter:blur(8px)}
.acm-chips svg{color:var(--acm-lime)}
.acm-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:2rem 1rem;text-align:center}
@media(min-width:768px){.acm-stats{grid-template-columns:repeat(4,1fr)}}
.acm-stats>div{display:flex;flex-direction:column-reverse}
.acm-stat-num{font-size:2.75rem;line-height:1;color:var(--acm-lime)}
@media(min-width:640px){.acm-stat-num{font-size:3.5rem}}
.acm-stat-label{margin-top:.5rem;font-size:.8rem;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.6)}
.acm-masonry{columns:2;column-gap:.6rem}
@media(min-width:640px){.acm-masonry{column-gap:1rem}}
@media(min-width:1024px){.acm-masonry{columns:3}}
.acm-masonry-item{break-inside:avoid;margin-bottom:.6rem}
@media(min-width:640px){.acm-masonry-item{margin-bottom:1rem}}
.acm-cap{background:linear-gradient(to top,rgba(0,0,0,.75),transparent);opacity:1;transition:opacity .3s}
@media(hover:hover){.acm-cap{opacity:0}.group:hover .acm-cap,.group:focus-visible .acm-cap,.acm-cap-on{opacity:1}}
.acm-grid-4{display:grid;gap:1rem;grid-template-columns:1fr}
@media(min-width:640px){.acm-grid-4{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.acm-grid-4{grid-template-columns:repeat(4,1fr)}}
.acm-tipo-img{aspect-ratio:4/3}
.acm-price{display:grid;gap:1rem;grid-template-columns:1fr}
@media(min-width:768px){.acm-price{grid-template-columns:repeat(3,1fr)}}
.acm-price li{border-radius:1rem;border:1px solid rgba(255,255,255,.1);background:var(--acm-card);padding:1.75rem;text-align:center}
.acm-price li:first-child{border-color:rgba(184,255,0,.35)}
.acm-price-k{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.5)}
.acm-price-v{margin-top:.5rem;font-size:2.5rem;line-height:1;text-transform:uppercase;color:var(--acm-lime)}
.acm-price-s{margin-top:.6rem;font-size:.9rem;color:rgba(255,255,255,.65)}
.acm-steps{display:grid;gap:1rem;grid-template-columns:1fr;counter-reset:s}
@media(min-width:640px){.acm-steps{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1024px){.acm-steps{grid-template-columns:repeat(4,1fr)}}
.acm-step{position:relative;display:flex;flex-direction:column;gap:.9rem;border-radius:1rem;border:1px solid rgba(255,255,255,.1);background:var(--acm-card);padding:1.75rem}
.acm-step-n{position:absolute;top:1.1rem;right:1.4rem;font-size:2.75rem;line-height:1;color:rgba(255,255,255,.08)}
@media(min-width:1024px){.acm-step:not(:last-child)::after{content:"";position:absolute;top:50%;right:-.75rem;width:.5rem;height:.5rem;border-top:2px solid var(--acm-lime);border-right:2px solid var(--acm-lime);transform:translateY(-50%) rotate(45deg)}}
.acm-reviews{display:grid;gap:1.25rem;grid-template-columns:1fr}
@media(min-width:768px){.acm-reviews{grid-template-columns:repeat(3,1fr)}}
.acm-dialog{pointer-events:auto}
.acm-lightbox{z-index:85}
.acm-ctas{width:100%}
@media(min-width:640px){.acm-ctas{width:auto}}
.acm-lb-fig{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 1rem;pointer-events:none}
.acm-lb-fig img{max-height:calc(100vh - 9rem);max-width:100%;border-radius:1rem;object-fit:contain;pointer-events:auto}
.acm-lb-btn{position:absolute;display:grid;place-items:center;width:2.75rem;height:2.75rem;border-radius:9999px;background:rgba(255,255,255,.08);color:#fff;transform:translateY(0)}
.acm-lb-btn[data-lb-prev],.acm-lb-btn[data-lb-next]{transform:translateY(-50%)}
.acm-lb-btn:hover{background:rgba(255,255,255,.16)}
@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;transition:none!important;animation:none!important}}
"""

# JS: réplica da lógica do modal/consentimento/atribuição do bundle (index-*.js) + UI da página.
SCRIPT = r"""
(function(){
var APPS_SCRIPT='https://script.google.com/macros/s/AKfycbwbFeRZgc0Nk5pMQymc-oK9ulKd5z0Skn5VkIThjCyXoAlaBnWoosO1FRjEF-oHZhLL/exec';
var WA_NUM='5567996879319';
function dl(o){window.dataLayer=window.dataLayer||[];window.dataLayer.push(o);}
function e164(v){var t=(v||'').replace(/\D/g,'').replace(/^0+/,'');return t.indexOf('55')===0&&(t.length===12||t.length===13)?t:(t.length===10||t.length===11?'55'+t:null);}
function msg(o){return 'Olá, meu nome é '+((o.nome||'').trim()||'um cliente')+', tenho interesse em '+((o.servico||'').trim()||'seus serviços')+'. Podemos conversar?';}
function waUrl(o){return 'https://wa.me/'+WA_NUM+'?text='+encodeURIComponent(msg(o));}
// atribuição (mesma chave da home: agl_attr)
var AK='agl_attr',UT=['utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid'];
try{var q=new URLSearchParams(location.search),old=JSON.parse(localStorage.getItem(AK)||'{}'),n=Object.assign({},old);
UT.forEach(function(k){var v=q.get(k);if(v)n[k]=v;});if(!old.landing_page)n.landing_page=location.href;if(old.referrer===undefined)n.referrer=document.referrer||'';
localStorage.setItem(AK,JSON.stringify(n));}catch(e){}
function attr(){try{return JSON.parse(localStorage.getItem(AK)||'{}');}catch(e){return {};}}
// consentimento (mesma chave da home: agl_consent_v1)
var CK='agl_consent_v1',banner=document.getElementById('cookie-banner');
function applyConsent(s){if(typeof window.gtag==='function')window.gtag('consent','update',{ad_storage:s,ad_user_data:s,ad_personalization:s,analytics_storage:s});dl({event:'consent_update',consent_state:s});}
try{var cs=localStorage.getItem(CK);if(cs==='granted'||cs==='denied')applyConsent(cs);else banner.hidden=false;}catch(e){banner.hidden=false;}
banner.addEventListener('click',function(ev){var b=ev.target.closest('[data-consent]');if(!b)return;var s=b.getAttribute('data-consent');try{localStorage.setItem(CK,s);}catch(e){}applyConsent(s);banner.hidden=true;});
// header: fundo ao rolar + menu mobile
var hd=document.querySelector('header'),SOLID=['border-b','border-white/5','bg-[#050505]/85','backdrop-blur-xl'];
function onScroll(){var s=window.scrollY>20;SOLID.forEach(function(c){hd.classList.toggle(c,s);});hd.classList.toggle('bg-transparent',!s);}
onScroll();window.addEventListener('scroll',onScroll,{passive:true});
var mb=document.querySelector('[data-menu]'),mm=document.getElementById('menu-mobile');
if(mb&&mm){mb.addEventListener('click',function(){var o=mm.hidden;mm.hidden=!o;mb.setAttribute('aria-expanded',String(o));hd.classList.toggle('bg-[#050505]/85',o||window.scrollY>20);});
mm.addEventListener('click',function(ev){if(ev.target.closest('a,button')){mm.hidden=true;mb.setAttribute('aria-expanded','false');}});}
// modal de orçamento
var modal=document.getElementById('lead-modal'),form=document.getElementById('form-lead'),body=document.getElementById('lead-body'),ok=document.getElementById('lead-ok');
var formName='modal_orcamento',started=false,sending=false,lastFocus=null,prevOverflow='';
function openModal(name){formName=name||'modal_orcamento';started=false;body.hidden=false;ok.hidden=true;setErr(false);lastFocus=document.activeElement;
modal.classList.remove('hidden');modal.setAttribute('aria-hidden','false');prevOverflow=document.body.style.overflow;document.body.style.overflow='hidden';
dl({event:'open_form',form_name:formName});setTimeout(function(){document.getElementById('lead-nome').focus();},60);}
function closeModal(){modal.classList.add('hidden');modal.setAttribute('aria-hidden','true');document.body.style.overflow=prevOverflow;if(lastFocus&&lastFocus.focus)lastFocus.focus();}
function setErr(on){var t=form.elements.namedItem('telefone');t.setAttribute('aria-invalid',String(on));t.classList.toggle('border-red-500',on);t.classList.toggle('border-white/10',!on);document.getElementById('lead-telefone-erro').hidden=!on;}
document.addEventListener('click',function(ev){var b=ev.target.closest('[data-lead]');if(b){ev.preventDefault();openModal(b.getAttribute('data-lead'));}});
modal.addEventListener('click',function(ev){if(ev.target.closest('[data-close]'))closeModal();});
document.addEventListener('keydown',function(ev){if(ev.key!=='Escape')return;if(!modal.classList.contains('hidden'))closeModal();else if(!lb.classList.contains('hidden'))lbClose();});
modal.addEventListener('keydown',function(ev){if(ev.key!=='Tab')return;var f=modal.querySelectorAll('button:not([hidden]),input:not([type=hidden]):not([hidden]),a[href]');f=Array.prototype.filter.call(f,function(x){return x.offsetParent!==null;});if(!f.length)return;var a=f[0],z=f[f.length-1];if(ev.shiftKey&&document.activeElement===a){ev.preventDefault();z.focus();}else if(!ev.shiftKey&&document.activeElement===z){ev.preventDefault();a.focus();}});
form.addEventListener('input',function(){if(!started){started=true;dl({event:'form_start',form_name:formName});}});
form.addEventListener('submit',function(ev){ev.preventDefault();if(sending)return;
var nome=form.elements.namedItem('nome').value.trim(),tel=form.elements.namedItem('telefone').value.trim(),servico=form.elements.namedItem('servico').value||'';
if(!nome){form.elements.namedItem('nome').focus();return;}
var w=e164(tel);if(!w){setErr(true);form.elements.namedItem('telefone').focus();return;}
setErr(false);sending=true;var btn=form.querySelector('[type=submit]');btn.disabled=true;btn.querySelector('[data-label]').textContent='Enviando...';
var l={nome:nome,whatsapp:w,servico:servico,id:'agl-'+Date.now()+'-'+Math.floor(Math.random()*1e6),formName:formName};
dl({event:'form_submit',lead_nome:l.nome,lead_whatsapp:l.whatsapp,lead_servico:l.servico,lead_id:l.id,form_name:l.formName,user_data:{phone_number:'+'+w}});
try{var a=attr();fetch(APPS_SCRIPT,{method:'POST',mode:'no-cors',headers:{'Content-Type':'text/plain;charset=utf-8'},body:JSON.stringify({nome:l.nome,whatsapp:l.whatsapp,servico:l.servico,mensagem:msg(l),pagina:location.href,source:l.formName,ts:Date.now(),lead_id:l.id,
utm_source:a.utm_source||'',utm_medium:a.utm_medium||'',utm_campaign:a.utm_campaign||'',utm_term:a.utm_term||'',utm_content:a.utm_content||'',gclid:a.gclid||'',wbraid:a.wbraid||'',gbraid:a.gbraid||'',landing_page:a.landing_page||'',referrer:a.referrer||''}),keepalive:true}).catch(function(){});}catch(e){}
var u=waUrl(l);window.open(u,'_blank','noopener,noreferrer');document.getElementById('lead-ok-link').href=waUrl({nome:'',servico:''});
sending=false;btn.disabled=false;btn.querySelector('[data-label]').textContent='Quero meu orçamento';body.hidden=true;ok.hidden=false;form.reset();});
// FAQ (mesmas classes do acordeão da home)
document.querySelectorAll('.acm-faq > h3 > button').forEach(function(b){b.addEventListener('click',function(){
var open=b.getAttribute('aria-expanded')!=='true';document.querySelectorAll('.acm-faq').forEach(function(it){var bb=it.querySelector('button'),p=it.querySelector('[role=region]'),on=open&&bb===b;
bb.setAttribute('aria-expanded',String(on));it.classList.toggle('border-[#b8ff00]/30',on);it.classList.toggle('bg-[#101010]',on);it.classList.toggle('border-white/10',!on);it.classList.toggle('bg-[#0a0a0a]',!on);
p.classList.toggle('grid-rows-[1fr]',on);p.classList.toggle('opacity-100',on);p.classList.toggle('grid-rows-[0fr]',!on);p.classList.toggle('opacity-0',!on);bb.querySelector('svg').classList.toggle('rotate-180',on);});});});
// lightbox da galeria
var lb=document.getElementById('lightbox'),lbImg=document.getElementById('lb-img'),lbCap=document.getElementById('lb-cap'),items=[].slice.call(document.querySelectorAll('[data-lb]')),cur=0,lbFocus=null;
function lbShow(i){cur=(i+items.length)%items.length;var im=items[cur].querySelector('img');lbImg.src=im.getAttribute('data-full');lbImg.alt=im.alt;lbCap.textContent=items[cur].querySelector('.line-clamp-1').textContent;}
function lbClose(){lb.classList.add('hidden');document.body.style.overflow='';if(lbFocus)lbFocus.focus();}
items.forEach(function(b,i){b.addEventListener('click',function(){lbFocus=b;lbShow(i);lb.classList.remove('hidden');document.body.style.overflow='hidden';lb.querySelector('[data-lb-close].acm-lb-btn').focus();});});
lb.addEventListener('click',function(ev){if(ev.target.closest('[data-lb-close]'))lbClose();else if(ev.target.closest('[data-lb-prev]'))lbShow(cur-1);else if(ev.target.closest('[data-lb-next]'))lbShow(cur+1);});
lb.addEventListener('keydown',function(ev){if(ev.key==='ArrowLeft')lbShow(cur-1);if(ev.key==='ArrowRight')lbShow(cur+1);});
// reel: só carrega e toca quando aparece na tela
var v=document.querySelector('.acm-reel');
if(v&&'IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){v.preload='auto';var p=v.play();if(p&&p.catch)p.catch(function(){});}else v.pause();});},{threshold:.35}).observe(v);}
})();
"""

JSONLD = {
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Service",
            "@id": f"{URL}#service",
            "name": "Fachada em ACM em Campo Grande MS",
            "serviceType": "Fachada em ACM",
            "description": DESC,
            "url": URL,
            "image": f"{SITE}/og-fachada-acm.jpg",
            "provider": {"@id": f"{SITE}/#business"},
            "areaServed": {"@type": "State", "name": "Mato Grosso do Sul"},
        },
        {
            "@type": "FAQPage",
            "@id": f"{URL}#faq",
            "mainEntity": [
                {"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": a}} for q, a in FAQ
            ],
        },
        {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {"@type": "ListItem", "position": 1, "name": "Início", "item": f"{SITE}/"},
                {"@type": "ListItem", "position": 2, "name": "Fachada em ACM", "item": URL},
            ],
        },
    ],
}

PAGE = f"""<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>{TITLE}</title>
<meta name="description" content="{html.escape(DESC)}"/>
<link rel="canonical" href="{URL}"/>
<meta name="theme-color" content="#050505"/><meta name="author" content="Agilize Comunicação Visual"/>
<meta property="og:type" content="website"/><meta property="og:url" content="{URL}"/><meta property="og:title" content="{TITLE}"/><meta property="og:description" content="{html.escape(DESC)}"/>
<meta property="og:image" content="{SITE}/og-fachada-acm.jpg"/><meta property="og:image:width" content="1200"/><meta property="og:image:height" content="630"/><meta property="og:locale" content="pt_BR"/>
<meta name="twitter:card" content="summary_large_image"/><meta name="twitter:title" content="{TITLE}"/><meta name="twitter:description" content="{html.escape(DESC)}"/><meta name="twitter:image" content="{SITE}/og-fachada-acm.jpg"/>
<link rel="preload" as="image" href="{IMG}/acm-hero-960.webp" media="(max-width: 767px)" fetchpriority="high"/>
<link rel="preload" as="image" href="{IMG}/acm-hero-1600.webp" media="(min-width: 768px)" fetchpriority="high"/>
<link rel="preload" as="image" href="{LOGO}"/>
<link rel="stylesheet" href="{CSS_HREF}"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;family=Anton&amp;display=swap"/>
{FAVICONS}
{HEAD_SNIPPETS}
<script type="application/ld+json">{json.dumps(JSONLD, ensure_ascii=False, separators=(",", ":"))}</script>
<style>{STYLE}</style>
</head><body>{GTM_NOSCRIPT}<div class="min-h-screen bg-[#050505] text-white">{header}<main>
{S_HERO}{S_NUMEROS}{S_GALERIA}{S_PORQUE}{S_PRECO}{S_TIPOS}{S_PROCESSO}{clientes}{S_AVALIACOES}{S_FAQ}{S_CTA}
</main>{footer}</div>{MODAL}{LIGHTBOX}{COOKIES}
<script>{SCRIPT}</script>
</body></html>
"""

out = OUT_DIR / "fachada-de-acm" / "index.html"
out.parent.mkdir(parents=True, exist_ok=True)
out.write_text(PAGE, encoding="utf-8")
print(f"ok: {out} ({len(PAGE.encode()) // 1024} KB)")
pend = [k for k, v in {"PRECO_M2": C.PRECO_M2, "PRAZO_DIAS": C.PRAZO_DIAS, "RESPOSTA_24H": C.RESPOSTA_24H,
                       "PROJETO_3D": C.PROJETO_3D, "GARANTIA": C.GARANTIA}.items() if not v]
if pend:
    print("pendente de confirmação com o cliente (usando versão sem número):", ", ".join(pend))
