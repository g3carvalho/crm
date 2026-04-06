import { PageHeader } from "@/components/ui/page-header";

export default function ConversasPage() {
  return (
    <section>
      <PageHeader title="Conversas" description="Caixa de entrada do WhatsApp integrada aos leads e à operação." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Inbox, atribuição, templates e respostas rápidas (em implementação).
      </div>
    </section>
  );
}
