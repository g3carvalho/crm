import { useMemo, useState } from 'react'
import Layout from '../components/Layout'
import { ETAPAS_NEGOCIO } from '../lib/constants'
import { formatarData, formatarMoeda } from '../lib/format'
import { contatosStore, negociosStore } from '../lib/storage'

export default function PipelinePage() {
  const [negocios, setNegocios] = useState(negociosStore.listar())
  const contatos = useMemo(() => contatosStore.listar(), [])

  const contatoPorId = (id) => contatos.find((c) => c.id === id)

  const atualizarEtapa = (negocioId, novaEtapa) => {
    const atualizado = negocios.map((n) =>
      n.id === negocioId ? { ...n, etapa: novaEtapa, atualizadoEm: new Date().toISOString() } : n,
    )
    setNegocios(atualizado)
    negociosStore.salvar(atualizado)
  }

  const mover = (negocio, direcao) => {
    const idx = ETAPAS_NEGOCIO.indexOf(negocio.etapa)
    const destino = ETAPAS_NEGOCIO[idx + direcao]
    if (destino) atualizarEtapa(negocio.id, destino)
  }

  return (
    <Layout titulo="Pipeline">
      <div className="grid gap-4 xl:grid-cols-5 md:grid-cols-2">
        {ETAPAS_NEGOCIO.map((etapa) => (
          <section
            key={etapa}
            className="rounded-xl bg-gray-100 p-3"
            onDragOver={(e) => e.preventDefault()}
            onDrop={(e) => {
              const negocioId = e.dataTransfer.getData('negocioId')
              if (negocioId) atualizarEtapa(negocioId, etapa)
            }}
          >
            <h3 className="mb-3 font-semibold">{etapa}</h3>
            <div className="space-y-3">
              {negocios.filter((n) => n.etapa === etapa).map((negocio) => {
                const contato = contatoPorId(negocio.contatoId)
                return (
                  <article
                    key={negocio.id}
                    draggable
                    onDragStart={(e) => e.dataTransfer.setData('negocioId', negocio.id)}
                    className="rounded-lg bg-white p-3 shadow-sm"
                  >
                    <p className="font-semibold">{contato?.nome || 'Contato removido'}</p>
                    <p className="text-sm text-gray-600">{negocio.titulo}</p>
                    <p className="text-sm">{formatarMoeda(negocio.valor)}</p>
                    <p className="text-xs text-gray-500">Origem: {contato?.origem || '-'}</p>
                    <p className="text-xs text-gray-500">Data: {formatarData(negocio.criadoEm)}</p>
                    <div className="mt-3 flex justify-between">
                      <button className="rounded border px-2 py-1" onClick={() => mover(negocio, -1)}>←</button>
                      <button className="rounded border px-2 py-1" onClick={() => mover(negocio, 1)}>→</button>
                    </div>
                  </article>
                )
              })}
            </div>
          </section>
        ))}
      </div>
    </Layout>
  )
}
