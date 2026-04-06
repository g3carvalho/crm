import { PageHeader } from "@/components/ui/page-header";

export default function MasterPage() {
  return (
    <section className="p-6">
      <PageHeader title="Área master" description="Administração global: tenants, módulos, integrações e suporte assistido." />
      <div className="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
        Listagem de clientes, suspensão, impersonação segura e templates por nicho (em implementação).
      </div>
    </section>
  );
}
