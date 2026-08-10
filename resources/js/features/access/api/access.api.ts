import { api } from "@/api/client";

export type Permission = { id: number; name: string };
export type Role = { id: number; name: string; permissions: Permission[] };
export type Staff = { id: number; name: string; email: string; status: string; roles: Role[] };

export const accessApi = {
    staff: async (): Promise<{ data: Staff[] }> => (await api.get("/admin/staff")).data,
    roles: async (): Promise<{ data: { roles: Role[]; permissions: Permission[] } }> => (await api.get("/admin/roles")).data,
    createStaff: async (values: Record<string, string>) => (await api.post("/admin/staff", values)).data,
    updateStaff: async (id: number, values: Record<string, string>) => (await api.patch(`/admin/staff/${id}`, values)).data,
    createRole: async (name: string, permissions: string[]) => (await api.post("/admin/roles", { name, permissions })).data,
    updateRole: async (id: number, name: string, permissions: string[]) => (await api.patch(`/admin/roles/${id}`, { name, permissions })).data,
};
