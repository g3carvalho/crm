import { PageHeader } from "@/components/ui/page-header";

export default function IntegracoesPage() {
  return (
    <section>
      <PageHeader title="Integrações" description="Saúde das conexões com WhatsApp Business Cloud API e Google Ads Data Manager." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Painel de status, credenciais e logs de integração (em implementação).
      </div>
    </section>
  );
}
