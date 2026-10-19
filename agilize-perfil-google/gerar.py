# Gera perfil-google.md e valida o limite de caracteres de cada campo do Perfil da Empresa.
import sys
U = "?utm_source=google&utm_medium=organic&utm_campaign=perfil-empresa"
def u(path, content): return f"https://agilizegrafica.com.br{path}{U}&utm_content={content}"

DESCRICAO = (
    "A Agilize Comunicação Visual faz fachadas comerciais em Campo Grande e em todo o Mato Grosso do Sul há mais de 15 anos. "
    "Cuidamos de tudo, do começo ao fim: vistoria grátis no local, projeto sob medida, fabricação própria e instalação. "
    "Trabalhamos com fachada em ACM, letra caixa, letreiro luminoso, painel com LED, totem, placa comercial, adesivo, envelopamento "
    "e comunicação visual interna. Já atendemos marcas como Bradesco, Itaú, Burger King, Honda e Suzano, e também lojas, clínicas, "
    "oficinas, restaurantes e escritórios da cidade. Peça seu orçamento sem compromisso."
)

SERVICOS = [
    ("Fachada em ACM", "Revestimento em alumínio composto que não desbota, não enferruja e deixa a fachada reta e moderna. Várias cores e acabamentos, com letra caixa e iluminação em LED. Vistoria grátis, fabricação própria e instalação em Campo Grande e todo o MS."),
    ("Fachada comercial completa", "Projeto, fabricação e instalação da fachada inteira da sua empresa: revestimento, letreiro, iluminação e placas. Uma empresa só cuida de tudo, do primeiro desenho ao último parafuso."),
    ("Letra caixa", "Letras em alto relevo para fachada e recepção, em ACM, acrílico ou aço inox, com ou sem iluminação. Dão peso à marca e são vistas de longe."),
    ("Letreiro luminoso e painel com LED", "Letreiros e painéis iluminados que deixam sua marca visível de dia e de noite. Iluminação em LED, econômica e durável."),
    ("Totem", "Totem de sinalização para frente de loja, posto, condomínio e estacionamento. Fabricado sob medida, com ou sem iluminação."),
    ("Placa comercial", "Placas para fachada, porta e sinalização de todos os tipos e tamanhos, feitas na nossa fábrica em Campo Grande."),
    ("Adesivo e envelopamento de vitrine", "Adesivos para vitrine, parede e porta, com impressão de alta qualidade e aplicação pela nossa equipe."),
    ("Comunicação visual interna", "Sinalização, painéis e decoração com a sua marca para recepção, salas e corredores."),
    ("Impressão digital", "Impressão em alta resolução para materiais de fachada, vitrine e ambiente interno."),
    ("Identidade visual", "Criação ou ajuste do logotipo e das cores da sua marca, pronto para aplicar na fachada e na comunicação da empresa."),
    ("Vistoria técnica grátis", "Nossa equipe vai até o local, mede, avalia a estrutura e apresenta a melhor solução para a sua fachada, sem compromisso."),
    ("Manutenção de fachada e letreiro", "Limpeza, troca de iluminação e reparo de fachadas em ACM, letras caixa e letreiros luminosos. [CONFIRMAR com o Luciano se oferecem]"),
]

PRODUTOS = [
    ("Fachada em ACM", "Fachada em ACM com projeto, fabricação própria e instalação em Campo Grande e todo o MS. Combina com letra caixa e iluminação em LED. Vistoria grátis e orçamento sem compromisso.", "Foto: acm-hero-1600.webp (Lavey Lavanderia)", u("/fachada-de-acm", "produto-acm")),
    ("Letra caixa iluminada", "Letras em relevo com iluminação em LED que deixam sua marca acesa à noite. Fabricadas sob medida na nossa fábrica em Campo Grande.", "Foto: acm-francos-cafe-1.webp (Franco's Café)", u("/", "produto-letra-caixa")),
    ("Totem em ACM", "Totem de sinalização em ACM, nas cores da sua marca, para a frente da loja ou do estacionamento. Fabricação própria e instalação.", "Foto: acm-lavey-totem-1.webp (Lavey Lavanderia)", u("/", "produto-totem")),
    ("Painel com iluminação em LED", "Painel recortado com luz indireta em LED para fachada ou recepção. Destaca a marca e decora o ambiente.", "Foto: acm-painel-led-mapa-1.webp", u("/", "produto-painel-led")),
]

POSTS = [
    ("Semana 1 · Fachada em ACM", "acm-hero-1600.webp (Lavey Lavanderia)", "Saiba mais", u("/fachada-de-acm", "post-acm"),
     "Fachada em ACM em Campo Grande: reta, moderna e sem desbotar com o sol do MS.\n\nA Lavey Lavanderia Express escolheu ACM branco com letra caixa vermelha em relevo. Resultado: a fachada é vista do outro lado da rua.\n\nFazemos tudo: vistoria grátis, projeto, fabricação própria e instalação. Veja mais fachadas em ACM no nosso site."),
    ("Semana 2 · Obra real em vídeo", "vídeo acm-blessed-things-reel.mp4 (Blessed Things)", "Ligar agora", "(botão de ligação)",
     "Obra entregue: Blessed Things.\n\nMarquise inteira revestida em ACM cinza, letra caixa dourada e spots embutidos no forro. Acabamento reto nas quinas, sem emenda aparente.\n\nQuer uma fachada assim? Chama a gente para uma vistoria grátis."),
    ("Semana 3 · Fachada que aparece à noite", "acm-francos-cafe-1.webp (Franco's Café)", "Saiba mais", u("/", "post-letreiro"),
     "Sua fachada some quando escurece?\n\nNa Franco's Café a letra caixa iluminada transformou a entrada em ponto de referência à noite. LED gasta pouco e dura anos.\n\nFabricamos letreiros luminosos, letra caixa e painéis com LED aqui em Campo Grande."),
    ("Semana 4 · Como funciona", "acm-blessed-things-1.webp (Blessed Things)", "Saiba mais", u("/fachada-de-acm", "post-processo"),
     "Como funciona uma fachada com a Agilize:\n\n1. Vistoria grátis no local\n2. Projeto sob medida\n3. Fabricação na nossa estrutura\n4. Instalação pela nossa equipe\n\nSem terceirizar nada e sem surpresa no meio do caminho. Atendemos Campo Grande e todo o MS."),
]

RESP_MODELO = [
    ("5 estrelas", "Obrigado pela avaliação, {nome}! Foi um prazer cuidar da fachada da sua empresa. Quando precisar de manutenção ou de um projeto novo, é só chamar a equipe da Agilize."),
    ("4 estrelas", "Obrigado pelo retorno, {nome}! Ficamos felizes com o resultado. Se algo pudesse ter sido melhor, conta pra gente pelo WhatsApp (67) 99687-9319 que queremos ouvir."),
    ("1 a 3 estrelas", "{nome}, sentimos muito pela sua experiência. Queremos entender o que aconteceu e resolver. Pode nos chamar no WhatsApp (67) 99687-9319 e falar direto com o responsável?"),
]
RESP_REAIS = [
    ("Abimael Mendes", "Obrigado, Abimael! Que bom que o prazo e o acabamento da fachada ficaram como você esperava. Conte com a Agilize sempre que precisar."),
    ("Ullia Taketa", "Obrigado, Ullia! Ficamos muito felizes que você gostou da sua fachada. Foi um prazer atender você."),
    ("Nayanne Chaves", "Obrigado, Nayanne! Atender rápido e entregar no prazo é o nosso compromisso. Volte sempre que precisar."),
    ("Silvana Kato", "Obrigado, Silvana! Trabalho de última hora com arte específica é desafio que a gente gosta. Que bom que a Aline cuidou de tudo e o prazo foi cumprido."),
]

LIM = {"descricao": 750, "servico_nome": 120, "servico_desc": 300, "produto_nome": 58, "produto_desc": 1000, "post": 1500, "resposta": 4096}
erros = []
def chk(tipo, txt, ref):
    n = len(txt)
    if n > LIM[tipo]: erros.append(f"{ref}: {n}/{LIM[tipo]}")
    if "—" in txt: erros.append(f"{ref}: travessão")
    return n

out = []
w = out.append
w("# Perfil da Empresa no Google: Agilize\n")
w("Tudo pronto para copiar e colar. Os números entre parênteses mostram caracteres usados / limite do Google, conferidos por script.\n")
w("## 1. Informações básicas\n")
w("| Campo | O que colocar |\n|---|---|")
w("| Nome | Exatamente como está na placa da fachada e no site. Hoje o perfil diz \"Agilize Gráfica & Comunicação Visual\" e o site diz \"Agilize Comunicação Visual\": **alinhar os dois** (o Google compara). Nunca acrescentar \"Campo Grande\" ou \"fachada\" no nome: dá suspensão |")
w("| Categoria principal | **Loja de placas e letreiros** (em inglês, *Sign shop*). Ao digitar, escolha a opção mais próxima que aparecer |")
w("| Categorias secundárias | Loja de letreiros de neon / letreiros luminosos · Gráfica · Designer gráfico · Fornecedor de toldos e coberturas **só se fizerem**. No máximo 4 a 5; categoria que não corresponde a serviço real atrapalha |")
w("| Endereço | Rua Padre João Crippa, 1688, Centro, Campo Grande/MS |")
w("| Área de atendimento | Campo Grande · Mato Grosso do Sul |")
w("| Horário | Seg a sex 8h às 18h · Sáb 8h às 12h (confirmar feriados com o Luciano) |")
w("| Telefone | (67) 99687-9319 |")
w(f"| Site | `{u('/', 'site')}` (o UTM mostra no GA4 e na planilha de contatos quem veio do perfil) |")
w("| WhatsApp (perfis sociais) | https://wa.me/5567996879319 |")
w("| Data de abertura | Pegar no cartão CNPJ 13.365.673/0001-97. Não chutar |")
w("| Atributos | Marcar só o que for verdade: atendimento no local, orçamento grátis, formas de pagamento (Pix, cartão), acessibilidade |\n")
n = chk("descricao", DESCRICAO, "descrição")
w(f"## 2. Descrição ({n}/750)\n")
w("```\n" + DESCRICAO + "\n```\n")
w("## 3. Serviços\n")
w("Cadastrar em Editar perfil > Serviços, como serviço personalizado.\n")
w("| Serviço | Descrição | Caracteres |\n|---|---|---|")
for nome, d in SERVICOS:
    chk("servico_nome", nome, nome); c = chk("servico_desc", d, nome)
    w(f"| {nome} | {d} | {c}/300 |")
w("")
w("## 4. Produtos (vitrine com foto e botão)\n")
for nome, d, foto, link in PRODUTOS:
    a = chk("produto_nome", nome, nome); c = chk("produto_desc", d, nome)
    w(f"**{nome}** ({a}/58)  \n{d} ({c}/1000)  \n{foto}  \nBotão \"Saiba mais\": `{link}`\n")
w("## 5. Fotos\n")
w("| Tipo | O que subir |\n|---|---|")
w("| Logo | logo branco sobre fundo escuro, quadrado (o do site está em `/assets/logo-agilize-branco-*.webp`) |")
w("| Capa | acm-hero-1600.webp (Lavey). Foto horizontal, sem texto por cima |")
w("| Trabalhos | As 12 fotos de ACM da LP (`img/acm/`) + as fachadas antigas da home (Pampa Toyota, Private CRED, O2 Fitness, Kings Sneakers...) |")
w("| Vídeo | acm-blessed-things-reel.mp4 |")
w("| **Faltando** | **Fachada da própria Agilize, equipe trabalhando e a fábrica.** \"Fabricação própria\" é o argumento principal e hoje não tem nenhuma foto que prove. Pedir ao Luciano 5 a 10 fotos pelo celular |\n")
w("Ritmo: 3 a 5 fotos novas por mês, sempre de obra real. Nome do arquivo e geolocalização na foto não fazem diferença no ranking: não perca tempo com isso.\n")
w("## 6. Postagens (4 semanas, uma por semana)\n")
for tit, foto, botao, link, txt in POSTS:
    c = chk("post", txt, tit)
    w(f"### {tit} ({c}/1500)\nFoto: {foto} · Botão: {botao} · Link: `{link}`\n\n```\n{txt}\n```\n")
w("## 7. Respostas às avaliações\n")
w("Responder todas, inclusive as antigas. Dentro de 48h daqui pra frente.\n")
w("### Avaliações que já aparecem no site (responder se ainda estiverem sem resposta)\n")
for nome, t in RESP_REAIS:
    c = chk("resposta", t, nome); w(f"**{nome}**\n```\n{t}\n```\n")
w("### Modelos (trocar {nome} e citar o serviço feito)\n")
for k, t in RESP_MODELO:
    chk("resposta", t, k); w(f"**{k}**\n```\n{t}\n```\n")
w("## 8. Rotina\n")
w("| Quando | O quê |\n|---|---|")
w("| Toda semana | 1 postagem · responder avaliações novas |")
w("| Todo mês | 3 a 5 fotos de obra · pedir avaliação a cada cliente entregue (link curto do perfil no WhatsApp) |")
w("| A cada 3 meses | Revisar horário, serviços e o relatório de desempenho do perfil |")

if erros:
    print("ERROS:", *erros, sep="\n"); sys.exit(1)
open(sys.argv[1], "w", encoding="utf-8").write("\n".join(out) + "\n")
print("ok", len(SERVICOS), "serviços,", len(PRODUTOS), "produtos,", len(POSTS), "posts")
