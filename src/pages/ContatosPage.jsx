import { useMemo, useState } from 'react'
import Botao from '../components/Botao'
import Input from '../components/Input'
import Layout from '../components/Layout'
import Modal from '../components/Modal'
import Tabela from '../components/Tabela'
import { ORIGENS, STATUS_CONTATO } from '../lib/constants'
import { formatarData } from '../lib/format'
import { contatosStore, negociosStore } from '../lib/storage'

const vazio = { nome: '', whatsapp: '', email: '', origem: ORIGENS[0], status: STATUS_CONTATO[0] }

export default function ContatosPage() {
  const [contatos, setContatos] = useState(contatosStore.listar())
  const [busca, setBusca] = useState('')
  const [filtroStatus, setFiltroStatus] = useState('')
  const [filtroOrigem, setFiltroOrigem] = useState('')
  const [ordem, setOrdem] = useState('nome')
  const [pagina, setPagina] = useState(1)
  const [aberto, setAberto] = useState(false)
  const [editandoId, setEditandoId] = useState(null)
  const [form, setForm] = useState(vazio)
  const [criarNegocio, setCriarNegocio] = useState(false)
  const [erro, setErro] = useState('')

  const filtrados = useMemo(() => {
    const termo = busca.toLowerCase()
    const lista = contatos
      .filter((c) => [c.nome, c.whatsapp, c.email].join(' ').toLowerCase().includes(termo))
      .filter((c) => (filtroStatus ? c.status === filtroStatus : true))
      .filter((c) => (filtroOrigem ? c.origem === filtroOrigem : true))

    return lista.sort((a, b) =>
      ordem === 'nome' ? a.nome.localeCompare(b.nome) : new Date(b.criadoEm) - new Date(a.criadoEm),
    )
  }, [contatos, busca, filtroStatus, filtroOrigem, ordem])

  const porPagina = 10
  const totalPaginas = Math.max(1, Math.ceil(filtrados.length / porPagina))
  const paginados = filtrados.slice((pagina - 1) * porPagina, pagina * porPagina)

  const persistirContatos = (lista) => {
    setContatos(lista)
    contatosStore.salvar(lista)
  }

  const abrirNovo = () => {
    setForm(vazio)
    setEditandoId(null)
    setErro('')
    setCriarNegocio(false)
    setAberto(true)
  }

  const abrirEdicao = (contato) => {
    setForm(contato)
    setEditandoId(contato.id)
    setErro('')
    setCriarNegocio(false)
    setAberto(true)
  }

  const salvar = (e) => {
    e.preventDefault()
    if (!form.nome.trim() || !form.whatsapp.trim() || !form.email.trim()) {
      setErro('Preencha nome, WhatsApp e e-mail.')
      return
    }

    let contatoId = editandoId
    const lista = [...contatos]

    if (editandoId) {
      const idx = lista.findIndex((c) => c.id === editandoId)
      lista[idx] = { ...lista[idx], ...form }
    } else {
      contatoId = crypto.randomUUID()
      lista.unshift({ ...form, id: contatoId, criadoEm: new Date().toISOString() })
    }

    persistirContatos(lista)

    if (criarNegocio && !editandoId) {
      const negocios = negociosStore.listar()
      negocios.unshift({
        id: crypto.randomUUID(),
        contatoId,
        titulo: `Oportunidade - ${form.nome}`,
        valor: 0,
        etapa: 'Novo',
        criadoEm: new Date().toISOString(),
        atualizadoEm: new Date().toISOString(),
      })
      negociosStore.salvar(negocios)
    }

    setAberto(false)
  }

  const excluir = (id) => {
    if (!window.confirm('Tem certeza que deseja excluir este contato?')) return
    persistirContatos(contatos.filter((c) => c.id !== id))
  }

  return (
    <Layout titulo="Contatos">
      <div className="space-y-4">
        <div className="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
          <Input label="Buscar" value={busca} onChange={(e) => setBusca(e.target.value)} className="min-w-52" />
          <label className="text-sm">Status<select className="ml-2 rounded border p-2" value={filtroStatus} onChange={(e) => setFiltroStatus(e.target.value)}><option value="">Todos</option>{STATUS_CONTATO.map((status) => <option key={status}>{status}</option>)}</select></label>
          <label className="text-sm">Origem<select className="ml-2 rounded border p-2" value={filtroOrigem} onChange={(e) => setFiltroOrigem(e.target.value)}><option value="">Todas</option>{ORIGENS.map((origem) => <option key={origem}>{origem}</option>)}</select></label>
          <label className="text-sm">Ordenar<select className="ml-2 rounded border p-2" value={ordem} onChange={(e) => setOrdem(e.target.value)}><option value="nome">Nome</option><option value="data">Data de criação</option></select></label>
          <Botao className="ml-auto" onClick={abrirNovo}>Novo contato</Botao>
        </div>

        <Tabela colunas={['Nome', 'WhatsApp', 'E-mail', 'Origem', 'Status', 'Ações']}>
          {paginados.map((contato) => (
            <tr key={contato.id} className="border-t">
              <td className="px-4 py-3">{contato.nome}<div className="text-xs text-gray-500">Criado em {formatarData(contato.criadoEm)}</div></td>
              <td className="px-4 py-3">{contato.whatsapp}</td>
              <td className="px-4 py-3">{contato.email}</td>
              <td className="px-4 py-3">{contato.origem}</td>
              <td className="px-4 py-3">{contato.status}</td>
              <td className="px-4 py-3">
                <div className="flex gap-2">
                  <Botao variante="secundario" onClick={() => abrirEdicao(contato)}>Editar</Botao>
                  <Botao variante="perigo" onClick={() => excluir(contato.id)}>Excluir</Botao>
                </div>
              </td>
            </tr>
          ))}
        </Tabela>

        <div className="flex items-center justify-between text-sm">
          <p>Página {pagina} de {totalPaginas}</p>
          <div className="flex gap-2">
            <Botao variante="secundario" onClick={() => setPagina((p) => Math.max(1, p - 1))}>Anterior</Botao>
            <Botao variante="secundario" onClick={() => setPagina((p) => Math.min(totalPaginas, p + 1))}>Próxima</Botao>
          </div>
        </div>
      </div>

      <Modal aberto={aberto} titulo={editandoId ? 'Editar contato' : 'Novo contato'} onClose={() => setAberto(false)}>
        <form className="space-y-3" onSubmit={salvar}>
          <Input label="Nome" value={form.nome} onChange={(e) => setForm((f) => ({ ...f, nome: e.target.value }))} />
          <Input label="WhatsApp" value={form.whatsapp} onChange={(e) => setForm((f) => ({ ...f, whatsapp: e.target.value }))} />
          <Input label="E-mail" type="email" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} />

          <div className="grid gap-3 md:grid-cols-2">
            <label className="text-sm">Origem<select className="mt-1 w-full rounded border p-2" value={form.origem} onChange={(e) => setForm((f) => ({ ...f, origem: e.target.value }))}>{ORIGENS.map((origem) => <option key={origem}>{origem}</option>)}</select></label>
            <label className="text-sm">Status<select className="mt-1 w-full rounded border p-2" value={form.status} onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}>{STATUS_CONTATO.map((status) => <option key={status}>{status}</option>)}</select></label>
          </div>

          {!editandoId && (
            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" checked={criarNegocio} onChange={(e) => setCriarNegocio(e.target.checked)} />
              Criar negócio agora
            </label>
          )}

          {erro && <p className="text-sm text-red-600">{erro}</p>}

          <div className="flex justify-end gap-2">
            <Botao variante="secundario" type="button" onClick={() => setAberto(false)}>Cancelar</Botao>
            <Botao type="submit">Salvar</Botao>
          </div>
        </form>
      </Modal>
    </Layout>
  )
}
