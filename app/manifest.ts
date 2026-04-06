import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "CRM Multiempresa",
    short_name: "CRM",
    description: "CRM com agenda, chat e automações",
    start_url: "/painel",
    display: "standalone",
    background_color: "#f8fafc",
    theme_color: "#4f46e5",
    lang: "pt-BR",
    icons: [
      {
        src: "/icon-192.png",
        sizes: "192x192",
        type: "image/png"
      },
      {
        src: "/icon-512.png",
        sizes: "512x512",
        type: "image/png"
      }
    ]
  };
}
