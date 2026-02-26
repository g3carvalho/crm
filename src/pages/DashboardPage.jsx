import { useMemo } from 'react'
import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
} from 'chart.js'
import { Bar, Line } from 'react-chartjs-2'
import CardMetrica from '../components/CardMetrica'
import Layout from '../components/Layout'
import { ETAPAS_NEGOCIO } from '../lib/constants'
import { formatarMoeda } from '../lib/format'
import { contatosStore, negociosStore } from '../lib/storage'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Tooltip, Legend)

export default function DashboardPage() {
  const dados = useMemo(() => {
    const contatos = contatosStore.listar()
    const negocios = negociosStore.listar()

    const leads = contatos.length
    const abertos = negocios.filter((n) => !['Ganho', 'Perdido'].includes(n.etapa))
    const ganhosPrevistos = abertos.reduce((acc, item) => acc + Number(item.valor || 0), 0)

    const ganhos = negocios.filter((n) => n.etapa === 'Ganho').length
    const baseConversao = negocios.filter((n) => n.etapa !== 'Perdido').length
    const taxaConversao = baseConversao ? `${Math.round((ganhos / baseConversao) * 100)}%` : '0%'

    const hoje = new Date()
    const labelsDias = [...Array(14)].map((_, i) => {
      const data = new Date(hoje)
      data.setDate(hoje.getDate() - (13 - i))
      return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
    })

    const contatosPorDia = labelsDias.map((label) => {
      return contatos.filter((contato) =>
        new Date(contato.criadoEm).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) === label,
      ).length
    })

    const negociosPorEtapa = ETAPAS_NEGOCIO.map(
      (etapa) => negocios.filter((negocio) => negocio.etapa === etapa).length,
    )

    return {
      leads,
      abertos: abertos.length,
      ganhosPrevistos,
      taxaConversao,
      linha: {
        labels: labelsDias,
        datasets: [{ label: 'Novos contatos', data: contatosPorDia, borderColor: '#6D28D9', backgroundColor: '#6D28D9' }],
      },
      barras: {
        labels: ETAPAS_NEGOCIO,
        datasets: [{ label: 'Negócios por etapa', data: negociosPorEtapa, backgroundColor: '#6D28D9' }],
      },
    }
  }, [])

  return (
    <Layout titulo="Dashboard">
      <div className="space-y-6">
        <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <CardMetrica titulo="Leads" valor={dados.leads} />
          <CardMetrica titulo="Negócios abertos" valor={dados.abertos} />
          <CardMetrica titulo="Ganhos previstos" valor={formatarMoeda(dados.ganhosPrevistos)} />
          <CardMetrica titulo="Taxa de conversão" valor={dados.taxaConversao} destaque />
        </section>
        <section className="grid gap-4 lg:grid-cols-2">
          <article className="rounded-xl bg-white p-4 shadow-sm"><h3 className="mb-2 font-semibold">Novos contatos (14 dias)</h3><Line data={dados.linha} /></article>
          <article className="rounded-xl bg-white p-4 shadow-sm"><h3 className="mb-2 font-semibold">Negócios por etapa</h3><Bar data={dados.barras} /></article>
        </section>
      </div>
    </Layout>
  )
}
