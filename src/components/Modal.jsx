import Botao from './Botao'

export default function Modal({ aberto, titulo, onClose, children }) {
  if (!aberto) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-lg font-semibold">{titulo}</h2>
          <Botao variante="secundario" onClick={onClose}>Fechar</Botao>
        </div>
        {children}
      </div>
    </div>
  )
}
