import { PageHeader } from "@/components/ui/page-header";

const indicadores = [
  "Total de leads",
  "Leads novos do dia",
  "Negociações por etapa",
  "Compromissos de hoje",
  "Atendimentos concluídos",
  "Taxa de fechamento"
];

export default function PainelPage() {
  return (
    <section>
      <PageHeader
        title="Painel geral"
        description="Visão consolidada de vendas, agenda e atendimento com filtros por período."
      />
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {indicadores.map((item) => (
          <article key={item} className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h3 className="text-sm font-medium text-slate-600">{item}</h3>
            <p className="mt-2 text-2xl font-semibold text-slate-900">—</p>
          </article>
        ))}
      </div>
    </section>
  );
}
