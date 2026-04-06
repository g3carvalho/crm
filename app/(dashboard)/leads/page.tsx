import { PageHeader } from "@/components/ui/page-header";

export default function LeadsPage() {
  return (
    <section>
      <PageHeader title="Leads" description="Lista completa de leads com origem, responsável, status e rastreio de campanha." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Tabela de leads com filtros e ações em lote (em implementação).
      </div>
    </section>
  );
}
