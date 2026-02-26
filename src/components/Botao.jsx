export default function Botao({ children, variante = 'primario', className = '', ...props }) {
  const estilos = {
    primario: 'bg-primario text-white hover:bg-violet-800',
    secundario: 'bg-white text-gray-800 border border-gray-300 hover:bg-gray-50',
    perigo: 'bg-red-600 text-white hover:bg-red-700',
  }

  return (
    <button
      className={`inline-flex items-center justify-center rounded-lg px-4 py-2 font-medium transition ${estilos[variante]} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}
