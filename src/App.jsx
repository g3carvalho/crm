import { Navigate, Route, Routes } from 'react-router-dom'
import LoginPage from './pages/LoginPage'
import InicioPage from './pages/InicioPage'
import DashboardPage from './pages/DashboardPage'
import PipelinePage from './pages/PipelinePage'
import ContatosPage from './pages/ContatosPage'
import { auth, seedInicial } from './lib/storage'

seedInicial()

function RotaPrivada({ children }) {
  return auth.token() ? children : <Navigate to="/login" replace />
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/inicio" element={<RotaPrivada><InicioPage /></RotaPrivada>} />
      <Route path="/dashboard" element={<RotaPrivada><DashboardPage /></RotaPrivada>} />
      <Route path="/pipeline" element={<RotaPrivada><PipelinePage /></RotaPrivada>} />
      <Route path="/contatos" element={<RotaPrivada><ContatosPage /></RotaPrivada>} />
      <Route path="*" element={<Navigate to={auth.token() ? '/inicio' : '/login'} replace />} />
    </Routes>
  )
}
