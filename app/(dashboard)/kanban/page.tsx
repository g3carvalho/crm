import { PageHeader } from "@/components/ui/page-header";

export default function KanbanPage() {
  return (
    <section>
      <PageHeader title="Kanban" description="Funis múltiplos com etapas configuráveis por cliente e histórico automático." />
      <div className="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">
        Quadro Kanban com drag-and-drop e histórico de etapa (em implementação).
      </div>
    </section>
  );
}
