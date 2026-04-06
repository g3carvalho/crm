export default function LoginPage() {
  return (
    <div className="mx-auto flex min-h-screen max-w-md items-center px-6">
      <form className="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 className="text-2xl font-bold text-slate-900">Entrar no CRM</h1>
        <p className="mt-1 text-sm text-slate-600">Acesso multiempresa seguro com Supabase Auth.</p>
        <div className="mt-6 space-y-4">
          <label className="block text-sm">
            <span className="mb-1 block font-medium">E-mail</span>
            <input className="w-full rounded-md border border-slate-300 px-3 py-2" type="email" />
          </label>
          <label className="block text-sm">
            <span className="mb-1 block font-medium">Senha</span>
            <input className="w-full rounded-md border border-slate-300 px-3 py-2" type="password" />
          </label>
        </div>
        <button className="mt-6 w-full rounded-md bg-brand-500 px-4 py-2 font-semibold text-white hover:bg-brand-700">
          Entrar
        </button>
      </form>
    </div>
  );
}
