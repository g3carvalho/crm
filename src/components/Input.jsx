export default function Input({ label, erro, className = '', ...props }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      {label && <span className="font-medium text-gray-700">{label}</span>}
      <input
        className={`w-full rounded-lg border px-3 py-2 outline-none focus:border-primario ${erro ? 'border-red-500' : 'border-gray-300'} ${className}`}
        {...props}
      />
      {erro && <span className="text-xs text-red-600">{erro}</span>}
    </label>
  )
}
