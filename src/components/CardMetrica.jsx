export default function CardMetrica({ titulo, valor, destaque }) {
  return (
    <article className="rounded-xl bg-white p-4 shadow-sm">
      <p className="text-sm text-gray-600">{titulo}</p>
      <p className={`mt-2 text-2xl font-bold ${destaque ? 'text-primario' : 'text-gray-900'}`}>{valor}</p>
    </article>
  )
}
