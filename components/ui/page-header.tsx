interface PageHeaderProps {
  title: string;
  description: string;
}

export function PageHeader({ title, description }: PageHeaderProps) {
  return (
    <header className="mb-6">
      <h2 className="text-2xl font-bold text-slate-900">{title}</h2>
      <p className="mt-1 text-sm text-slate-600">{description}</p>
    </header>
  );
}
