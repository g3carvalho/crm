import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useState } from 'react'
import Botao from './Botao'
import { auth } from '../lib/storage'

const itensMenu = [
  { rota: '/inicio', label: 'Início', icon: '🏠' },
  { rota: '/dashboard', label: 'Dashboard', icon: '📊' },
  { rota: '/pipeline', label: 'Pipeline', icon: '🧩' },
  { rota: '/contatos', label: 'Contatos', icon: '👥' },
]

export default function Layout({ titulo, children }) {
  const [aberto, setAberto] = useState(false)
  const location = useLocation()
  const navigate = useNavigate()

  const sair = () => {
    auth.logout()
    navigate('/login')
  }

  return (
    <div className="min-h-screen md:flex">
      <aside className="hidden w-64 bg-white p-4 shadow md:block">
        <h1 className="mb-6 text-xl font-bold text-primario">CRM Interno</h1>
        <nav className="space-y-2">
          {itensMenu.map((item) => (
            <Link
              key={item.rota}
              to={item.rota}
              className={`flex items-center gap-2 rounded-lg px-3 py-2 ${location.pathname === item.rota ? 'bg-violet-100 text-primario' : 'hover:bg-gray-100'}`}
            >
              <span>{item.icon}</span> {item.label}
            </Link>
          ))}
        </nav>
        <Botao className="mt-6 w-full" variante="secundario" onClick={sair}>Sair</Botao>
      </aside>

      <div className="flex-1">
        <header className="sticky top-0 z-40 flex items-center justify-between bg-white px-4 py-3 shadow md:px-6">
          <div className="flex items-center gap-3">
            <button className="rounded border px-2 py-1 md:hidden" onClick={() => setAberto(true)}>
              ☰
            </button>
            <h2 className="text-lg font-semibold">{titulo}</h2>
          </div>
          <Botao variante="secundario" onClick={sair}>Sair</Botao>
        </header>

        {aberto && (
          <div className="fixed inset-0 z-50 md:hidden">
            <button className="absolute inset-0 bg-black/40" onClick={() => setAberto(false)}></button>
            <aside className="absolute left-0 top-0 h-full w-64 bg-white p-4 shadow-lg">
              <h1 className="mb-6 text-xl font-bold text-primario">CRM Interno</h1>
              <nav className="space-y-2">
                {itensMenu.map((item) => (
                  <Link
                    key={item.rota}
                    to={item.rota}
                    onClick={() => setAberto(false)}
                    className={`flex items-center gap-2 rounded-lg px-3 py-2 ${location.pathname === item.rota ? 'bg-violet-100 text-primario' : 'hover:bg-gray-100'}`}
                  >
                    <span>{item.icon}</span> {item.label}
                  </Link>
                ))}
              </nav>
            </aside>
          </div>
        )}

        <main className="p-4 md:p-6">{children}</main>
      </div>
    </div>
  )
}
