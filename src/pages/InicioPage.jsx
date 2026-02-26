import { useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import Botao from '../components/Botao'
import CardMetrica from '../components/CardMetrica'
import Layout from '../components/Layout'
import { contatosStore, negociosStore } from '../lib/storage'

export default function InicioPage() {
  const navigate = useNavigate()
  const { contatos, negociosAbertos } = useMemo(() => {
    const contatosLista = contatosStore.listar()
    const negociosLista = negociosStore.listar().filter((n) => !['Ganho', 'Perdido'].includes(n.etapa))
    return { contatos: contatosLista.length, negociosAbertos: negociosLista.length }
  }, [])

  return (
    <Layout titulo="Início">
      <div className="space-y-6">
        <section className="rounded-xl bg-white p-6 shadow-sm">
          <h3 className="text-2xl font-bold">Bem-vindo ao CRM</h3>
          <p className="mt-2 text-gray-600">Gerencie contatos e acompanhe negócios com simplicidade.</p>
          <div className="mt-4 flex flex-wrap gap-3">
            <Botao onClick={() => navigate('/contatos')}>Novo contato</Botao>
            <Botao variante="secundario" onClick={() => navigate('/pipeline')}>Novo negócio</Botao>
          </div>
        </section>

        <section className="grid gap-4 md:grid-cols-2">
          <CardMetrica titulo="Total de contatos" valor={contatos} destaque />
          <CardMetrica titulo="Negócios em aberto" valor={negociosAbertos} />
        </section>
      </div>
    </Layout>
  )
}
