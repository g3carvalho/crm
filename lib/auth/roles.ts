export const ROLES = ["superadmin", "owner", "manager", "agent", "viewer"] as const;

export type Role = (typeof ROLES)[number];

const roleRank: Record<Role, number> = {
  superadmin: 5,
  owner: 4,
  manager: 3,
  agent: 2,
  viewer: 1
};

export function hasAtLeastRole(current: Role, minimum: Role) {
  return roleRank[current] >= roleRank[minimum];
}
