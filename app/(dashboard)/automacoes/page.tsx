import { PageHeader } from "@/components/ui/page-header";

export default function AutomacoesPage() {
  return (
    <section>
      <PageHeader title="Automações" description="Regras para lembretes, confirmações, avaliações e notificações da agenda." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Editor de gatilhos + ações com retries e logs (em implementação).
      </div>
    </section>
  );
}
