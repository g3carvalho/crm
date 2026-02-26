import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import Botao from '../components/Botao'
import Input from '../components/Input'
import { auth } from '../lib/storage'

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export default function LoginPage() {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [senha, setSenha] = useState('')
  const [erros, setErros] = useState({})

  if (auth.token()) return <Navigate to="/inicio" replace />

  const onSubmit = (e) => {
    e.preventDefault()
    const novosErros = {}

    if (!emailRegex.test(email)) novosErros.email = 'Digite um e-mail válido.'
    if (senha.length < 6) novosErros.senha = 'A senha deve ter pelo menos 6 caracteres.'

    setErros(novosErros)

    if (Object.keys(novosErros).length === 0) {
      auth.login()
      navigate('/inicio')
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center p-4">
      <form onSubmit={onSubmit} className="w-full max-w-md rounded-xl bg-white p-6 shadow">
        <h1 className="mb-1 text-2xl font-bold text-primario">CRM Interno</h1>
        <p className="mb-6 text-sm text-gray-600">Entre com seu e-mail e senha para acessar.</p>

        <div className="space-y-4">
          <Input label="E-mail" type="email" value={email} onChange={(e) => setEmail(e.target.value)} erro={erros.email} />
          <Input label="Senha" type="password" value={senha} onChange={(e) => setSenha(e.target.value)} erro={erros.senha} />
          <Botao type="submit" className="w-full">Entrar</Botao>
        </div>
      </form>
    </div>
  )
}
