import crypto from "node:crypto";

export function verifyMetaSignature(payload: string, signature: string | null, appSecret: string) {
  if (!signature?.startsWith("sha256=")) return false;

  const incoming = signature.replace("sha256=", "");
  const expected = crypto.createHmac("sha256", appSecret).update(payload, "utf8").digest("hex");

  return crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(incoming));
}
