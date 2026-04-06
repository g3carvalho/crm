import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "CRM Multiempresa",
  description: "CRM com agenda, chat e automações para prestadores de serviço"
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="pt-BR">
      <body>{children}</body>
    </html>
  );
}
