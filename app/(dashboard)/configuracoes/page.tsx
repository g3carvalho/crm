import { PageHeader } from "@/components/ui/page-header";

export default function ConfiguracoesPage() {
  return (
    <section>
      <PageHeader title="Configurações" description="Usuários, permissões, unidades, serviços e parâmetros de negócio." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Gestão de perfis: dono, gerente, atendente e visualizador (em implementação).
      </div>
    </section>
  );
}
