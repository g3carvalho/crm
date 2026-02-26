import { CONTATOS_KEY, ETAPAS_NEGOCIO, NEGOCIOS_KEY, ORIGENS, STATUS_CONTATO, TOKEN_KEY } from './constants'

const hoje = new Date()
const diasAtras = (dias) => new Date(hoje.getTime() - dias * 24 * 60 * 60 * 1000).toISOString()

const contatosSeed = [
  { id: crypto.randomUUID(), nome: 'Marina Silva', whatsapp: '11999990001', email: 'marina@empresa.com', origem: ORIGENS[0], status: STATUS_CONTATO[0], criadoEm: diasAtras(2) },
  { id: crypto.randomUUID(), nome: 'Rafael Costa', whatsapp: '11999990002', email: 'rafael@empresa.com', origem: ORIGENS[1], status: STATUS_CONTATO[0], criadoEm: diasAtras(4) },
  { id: crypto.randomUUID(), nome: 'Juliana Souza', whatsapp: '11999990003', email: 'juliana@empresa.com', origem: ORIGENS[2], status: STATUS_CONTATO[1], criadoEm: diasAtras(8) },
]

const negociosSeed = contatosSeed.map((contato, idx) => ({
  id: crypto.randomUUID(),
  contatoId: contato.id,
  titulo: `Negócio ${idx + 1}`,
  valor: (idx + 1) * 2500,
  etapa: ETAPAS_NEGOCIO[idx] || ETAPAS_NEGOCIO[0],
  criadoEm: diasAtras(idx + 1),
  atualizadoEm: diasAtras(idx + 1),
}))

const lerJSON = (chave, fallback = []) => {
  const conteudo = localStorage.getItem(chave)
  if (!conteudo) return fallback
  try {
    return JSON.parse(conteudo)
  } catch {
    return fallback
  }
}

const salvarJSON = (chave, valor) => localStorage.setItem(chave, JSON.stringify(valor))

export const seedInicial = () => {
  if (!localStorage.getItem(CONTATOS_KEY)) salvarJSON(CONTATOS_KEY, contatosSeed)
  if (!localStorage.getItem(NEGOCIOS_KEY)) salvarJSON(NEGOCIOS_KEY, negociosSeed)
}

export const auth = {
  token: () => localStorage.getItem(TOKEN_KEY),
  login: () => localStorage.setItem(TOKEN_KEY, `token_${Date.now()}`),
  logout: () => localStorage.removeItem(TOKEN_KEY),
}

export const contatosStore = {
  listar: () => lerJSON(CONTATOS_KEY),
  salvar: (lista) => salvarJSON(CONTATOS_KEY, lista),
}

export const negociosStore = {
  listar: () => lerJSON(NEGOCIOS_KEY),
  salvar: (lista) => salvarJSON(NEGOCIOS_KEY, lista),
}
