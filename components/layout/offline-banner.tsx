"use client";

import { useEffect, useState } from "react";

export function OfflineBanner() {
  const [online, setOnline] = useState(true);

  useEffect(() => {
    setOnline(navigator.onLine);
    const onUp = () => setOnline(true);
    const onDown = () => setOnline(false);
    window.addEventListener("online", onUp);
    window.addEventListener("offline", onDown);
    return () => {
      window.removeEventListener("online", onUp);
      window.removeEventListener("offline", onDown);
    };
  }, []);

  if (online) return null;

  return (
    <div className="border-b border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800">
      Você está offline. Novos leads e agendamentos serão sincronizados quando a conexão voltar.
    </div>
  );
}
