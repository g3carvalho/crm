import { PageHeader } from "@/components/ui/page-header";

export default function AgendaPage() {
  return (
    <section>
      <PageHeader title="Agenda" description="Visões diária, semanal e mensal com filtros por profissional e unidade." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Grade de horários e status de agendamento (em implementação).
      </div>
    </section>
  );
}
