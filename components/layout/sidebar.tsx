import Link from "next/link";

const links = [
  { href: "/painel", label: "Painel" },
  { href: "/leads", label: "Leads" },
  { href: "/kanban", label: "Kanban" },
  { href: "/agenda", label: "Agenda" },
  { href: "/conversas", label: "Conversas" },
  { href: "/automacoes", label: "Automações" },
  { href: "/integracoes", label: "Integrações" },
  { href: "/configuracoes", label: "Configurações" },
  { href: "/master", label: "Área master" }
];

export function Sidebar() {
  return (
    <aside className="w-full border-r border-slate-200 bg-white md:w-64">
      <div className="border-b border-slate-200 p-4">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">CRM Multiempresa</p>
        <h1 className="text-lg font-bold text-slate-900">Operações</h1>
      </div>
      <nav className="flex flex-col gap-1 p-3">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className="rounded-md px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
          >
            {link.label}
          </Link>
        ))}
      </nav>
    </aside>
  );
}
