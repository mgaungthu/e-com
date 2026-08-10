import { type FormEvent, useEffect, useMemo, useState } from "react";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import axios from "axios";
import {
    CheckCircle2,
    LockKeyhole,
    Plus,
    Save,
    ShieldCheck,
    UserPlus,
    Users,
} from "lucide-react";

import PageHeader from "@/components/PageHeader";
import {
    FormErrorAlert,
    FormField,
    FormSection,
    SelectInput,
    TextInput,
} from "@/components/forms";
import {
    accessApi,
    type Role,
    type Staff,
} from "@/features/access/api/access.api";
import { useAuthStore } from "@/store/authStore";

type ApiError = {
    message?: string;
    errors?: Record<string, string[]>;
};

type StaffFormValues = {
    name: string;
    email: string;
    password: string;
    status: string;
    role: string;
};

const initialStaffForm: StaffFormValues = {
    name: "",
    email: "",
    password: "",
    status: "active",
    role: "admin",
};

const staffStatuses = ["active", "inactive", "blocked", "pending"];

function errorMessage(error: unknown, fallback: string) {
    if (axios.isAxiosError<ApiError>(error)) {
        const response = error.response?.data;
        const validationMessage = response?.errors
            ? Object.values(response.errors)[0]?.[0]
            : null;

        return validationMessage ?? response?.message ?? fallback;
    }

    return fallback;
}

function displayName(value: string) {
    return value
        .split(/[._]/)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
}

export default function AccessManagementPage() {
    const queryClient = useQueryClient();
    const canManage = useAuthStore(
        (state) =>
            state.user?.permissions.includes("staff.manage") ?? false,
    );
    const staffQuery = useQuery({
        queryKey: ["staff"],
        queryFn: accessApi.staff,
    });
    const rolesQuery = useQuery({
        queryKey: ["roles"],
        queryFn: accessApi.roles,
    });
    const [staffForm, setStaffForm] =
        useState<StaffFormValues>(initialStaffForm);
    const [roleName, setRoleName] = useState("");
    const [actionError, setActionError] = useState<string | null>(null);
    const [actionMessage, setActionMessage] = useState<string | null>(null);

    const invalidateAccess = async () => {
        await Promise.all([
            queryClient.invalidateQueries({ queryKey: ["staff"] }),
            queryClient.invalidateQueries({ queryKey: ["roles"] }),
        ]);
    };

    const createStaff = useMutation({
        mutationFn: accessApi.createStaff,
        onSuccess: invalidateAccess,
    });
    const updateStaff = useMutation({
        mutationFn: ({
            id,
            values,
        }: {
            id: number;
            values: Record<string, string>;
        }) => accessApi.updateStaff(id, values),
        onSuccess: invalidateAccess,
    });
    const createRole = useMutation({
        mutationFn: ({
            name,
            permissions,
        }: {
            name: string;
            permissions: string[];
        }) => accessApi.createRole(name, permissions),
        onSuccess: invalidateAccess,
    });
    const updateRole = useMutation({
        mutationFn: ({
            id,
            name,
            permissions,
        }: {
            id: number;
            name: string;
            permissions: string[];
        }) => accessApi.updateRole(id, name, permissions),
        onSuccess: invalidateAccess,
    });

    const roles = rolesQuery.data?.data.roles ?? [];
    const permissions = rolesQuery.data?.data.permissions ?? [];
    const staff = staffQuery.data?.data ?? [];

    useEffect(() => {
        if (
            roles.length > 0 &&
            !roles.some((role) => role.name === staffForm.role)
        ) {
            setStaffForm((current) => ({
                ...current,
                role: roles[0].name,
            }));
        }
    }, [roles, staffForm.role]);

    function clearFeedback() {
        setActionError(null);
        setActionMessage(null);
    }

    async function handleCreateStaff(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        clearFeedback();

        try {
            await createStaff.mutateAsync({ ...staffForm });
            setStaffForm({
                ...initialStaffForm,
                role: roles[0]?.name ?? "admin",
            });
            setActionMessage("Staff member created successfully.");
        } catch (error) {
            setActionError(errorMessage(error, "Unable to create staff member."));
        }
    }

    async function handleCreateRole(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        clearFeedback();

        try {
            await createRole.mutateAsync({
                name: roleName,
                permissions: [],
            });
            setRoleName("");
            setActionMessage("Role created successfully.");
        } catch (error) {
            setActionError(errorMessage(error, "Unable to create role."));
        }
    }

    async function handleUpdateStaff(
        id: number,
        values: Record<string, string>,
    ) {
        clearFeedback();

        try {
            await updateStaff.mutateAsync({ id, values });
            setActionMessage("Staff member updated successfully.");
        } catch (error) {
            setActionError(errorMessage(error, "Unable to update staff member."));
        }
    }

    async function handleUpdateRole(
        role: Role,
        selectedPermissions: string[],
    ) {
        clearFeedback();

        try {
            await updateRole.mutateAsync({
                id: role.id,
                name: role.name,
                permissions: selectedPermissions,
            });
            setActionMessage(`${displayName(role.name)} permissions saved.`);
        } catch (error) {
            setActionError(errorMessage(error, "Unable to update permissions."));
        }
    }

    if (staffQuery.isLoading || rolesQuery.isLoading) {
        return (
            <div className="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                Loading access management...
            </div>
        );
    }

    if (staffQuery.isError || rolesQuery.isError) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-10 text-center">
                <p className="text-sm font-medium text-red-700">
                    Unable to load roles and staff.
                </p>
                <button
                    type="button"
                    onClick={() => {
                        void staffQuery.refetch();
                        void rolesQuery.refetch();
                    }}
                    className="mt-3 text-sm font-semibold text-blue-600 hover:text-blue-700"
                >
                    Try again
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <PageHeader
                title="Roles & Permissions"
                description="Manage staff accounts, roles, and permission assignments."
            />

            <FormErrorAlert message={actionError} />

            {actionMessage ? (
                <div className="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    <CheckCircle2 size={18} />
                    {actionMessage}
                </div>
            ) : null}

            {canManage ? (
                <form onSubmit={handleCreateStaff}>
                    <FormSection
                        title="Add staff member"
                        description="Create an admin account and assign its initial role."
                        icon={<UserPlus size={20} />}
                        contentClassName="grid gap-5 p-5 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <FormField label="Full name" htmlFor="staff_name" required>
                            <TextInput
                                id="staff_name"
                                value={staffForm.name}
                                onChange={(event) =>
                                    setStaffForm((current) => ({
                                        ...current,
                                        name: event.target.value,
                                    }))
                                }
                                disabled={createStaff.isPending}
                                placeholder="Staff member name"
                            />
                        </FormField>

                        <FormField label="Email address" htmlFor="staff_email" required>
                            <TextInput
                                id="staff_email"
                                type="email"
                                value={staffForm.email}
                                onChange={(event) =>
                                    setStaffForm((current) => ({
                                        ...current,
                                        email: event.target.value,
                                    }))
                                }
                                disabled={createStaff.isPending}
                                placeholder="staff@example.com"
                            />
                        </FormField>

                        <FormField
                            label="Temporary password"
                            htmlFor="staff_password"
                            required
                            helperText="Use at least 8 characters."
                        >
                            <TextInput
                                id="staff_password"
                                type="password"
                                value={staffForm.password}
                                onChange={(event) =>
                                    setStaffForm((current) => ({
                                        ...current,
                                        password: event.target.value,
                                    }))
                                }
                                disabled={createStaff.isPending}
                                minLength={8}
                                placeholder="Minimum 8 characters"
                            />
                        </FormField>

                        <FormField label="Role" htmlFor="staff_role" required>
                            <SelectInput
                                id="staff_role"
                                value={staffForm.role}
                                onChange={(event) =>
                                    setStaffForm((current) => ({
                                        ...current,
                                        role: event.target.value,
                                    }))
                                }
                                disabled={createStaff.isPending}
                            >
                                {roles.map((role) => (
                                    <option key={role.id} value={role.name}>
                                        {displayName(role.name)}
                                    </option>
                                ))}
                            </SelectInput>
                        </FormField>

                        <FormField label="Status" htmlFor="staff_status" required>
                            <SelectInput
                                id="staff_status"
                                value={staffForm.status}
                                onChange={(event) =>
                                    setStaffForm((current) => ({
                                        ...current,
                                        status: event.target.value,
                                    }))
                                }
                                disabled={createStaff.isPending}
                            >
                                {staffStatuses.map((status) => (
                                    <option key={status} value={status}>
                                        {displayName(status)}
                                    </option>
                                ))}
                            </SelectInput>
                        </FormField>

                        <div className="flex items-end">
                            <button
                                type="submit"
                                disabled={createStaff.isPending}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Plus size={18} />
                                {createStaff.isPending
                                    ? "Creating staff..."
                                    : "Create staff"}
                            </button>
                        </div>
                    </FormSection>
                </form>
            ) : null}

            <FormSection
                title="Staff members"
                description={`${staff.length} staff account${staff.length === 1 ? "" : "s"}`}
                icon={<Users size={20} />}
                contentClassName="p-0"
            >
                {staff.length === 0 ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        No staff members found.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    {[
                                        "Staff member",
                                        "Role",
                                        "Status",
                                        "Actions",
                                    ].map((heading) => (
                                        <th
                                            key={heading}
                                            className={`px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 ${heading === "Actions" ? "text-right" : "text-left"}`}
                                        >
                                            {heading}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {staff.map((member) => (
                                    <StaffRow
                                        key={member.id}
                                        staff={member}
                                        roles={roles}
                                        canManage={canManage}
                                        isSaving={updateStaff.isPending}
                                        onSave={(values) =>
                                            handleUpdateStaff(member.id, values)
                                        }
                                    />
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </FormSection>

            {canManage ? (
                <form onSubmit={handleCreateRole}>
                    <FormSection
                        title="Create custom role"
                        description="Add a role, then select its permissions below."
                        icon={<ShieldCheck size={20} />}
                        contentClassName="flex flex-col gap-4 p-5 sm:flex-row sm:items-end"
                    >
                        <FormField
                            label="Role name"
                            htmlFor="role_name"
                            helperText="Lowercase letters, numbers, and underscores only."
                            className="w-full max-w-md"
                        >
                            <TextInput
                                id="role_name"
                                value={roleName}
                                onChange={(event) =>
                                    setRoleName(
                                        event.target.value
                                            .toLowerCase()
                                            .replace(/[^a-z0-9_]/g, "_"),
                                    )
                                }
                                disabled={createRole.isPending}
                                placeholder="warehouse_manager"
                            />
                        </FormField>

                        <button
                            type="submit"
                            disabled={!roleName || createRole.isPending}
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Plus size={18} />
                            {createRole.isPending ? "Creating..." : "Create role"}
                        </button>
                    </FormSection>
                </form>
            ) : null}

            <div className="space-y-4">
                {roles.map((role) => (
                    <RoleCard
                        key={role.id}
                        role={role}
                        allPermissions={permissions.map(
                            (permission) => permission.name,
                        )}
                        canManage={canManage}
                        isSaving={updateRole.isPending}
                        onSave={(selected) =>
                            handleUpdateRole(role, selected)
                        }
                    />
                ))}
            </div>
        </div>
    );
}

function StaffRow({
    staff,
    roles,
    canManage,
    isSaving,
    onSave,
}: {
    staff: Staff;
    roles: Role[];
    canManage: boolean;
    isSaving: boolean;
    onSave: (values: Record<string, string>) => Promise<void>;
}) {
    const [role, setRole] = useState(staff.roles[0]?.name ?? "");
    const [status, setStatus] = useState(staff.status);

    useEffect(() => {
        setRole(staff.roles[0]?.name ?? "");
        setStatus(staff.status);
    }, [staff.roles, staff.status]);

    return (
        <tr className="transition hover:bg-slate-50">
            <td className="px-5 py-4">
                <p className="font-medium text-slate-900">{staff.name}</p>
                <p className="mt-0.5 text-sm text-slate-500">{staff.email}</p>
            </td>
            <td className="min-w-52 px-5 py-4">
                <SelectInput
                    aria-label={`Role for ${staff.name}`}
                    disabled={!canManage || isSaving}
                    value={role}
                    onChange={(event) => setRole(event.target.value)}
                    className="py-2"
                >
                    {roles.map((item) => (
                        <option key={item.id} value={item.name}>
                            {displayName(item.name)}
                        </option>
                    ))}
                </SelectInput>
            </td>
            <td className="min-w-40 px-5 py-4">
                <SelectInput
                    aria-label={`Status for ${staff.name}`}
                    disabled={!canManage || isSaving}
                    value={status}
                    onChange={(event) => setStatus(event.target.value)}
                    className="py-2"
                >
                    {staffStatuses.map((item) => (
                        <option key={item} value={item}>
                            {displayName(item)}
                        </option>
                    ))}
                </SelectInput>
            </td>
            <td className="px-5 py-4 text-right">
                {canManage ? (
                    <button
                        type="button"
                        onClick={() =>
                            void onSave({
                                name: staff.name,
                                email: staff.email,
                                password: "",
                                role,
                                status,
                            })
                        }
                        disabled={isSaving}
                        className="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save size={16} />
                        Save
                    </button>
                ) : (
                    <span className="text-sm text-slate-400">View only</span>
                )}
            </td>
        </tr>
    );
}

function RoleCard({
    role,
    allPermissions,
    canManage,
    isSaving,
    onSave,
}: {
    role: Role;
    allPermissions: string[];
    canManage: boolean;
    isSaving: boolean;
    onSave: (values: string[]) => Promise<void>;
}) {
    const [selected, setSelected] = useState(
        role.permissions.map((permission) => permission.name),
    );
    const locked = role.name === "super_admin";

    useEffect(() => {
        setSelected(role.permissions.map((permission) => permission.name));
    }, [role.permissions]);

    const permissionGroups = useMemo(() => {
        return allPermissions.reduce<Record<string, string[]>>(
            (groups, permission) => {
                const [group] = permission.split(".");
                groups[group] = [...(groups[group] ?? []), permission];
                return groups;
            },
            {},
        );
    }, [allPermissions]);

    const selectedCount = locked ? allPermissions.length : selected.length;

    return (
        <FormSection
            title={displayName(role.name)}
            description={`${selectedCount} of ${allPermissions.length} permissions enabled`}
            icon={
                locked ? <LockKeyhole size={20} /> : <ShieldCheck size={20} />
            }
            headerClassName="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4"
            contentClassName="space-y-5 p-5"
        >
            {locked ? (
                <p className="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                    Super Admin always has every permission and cannot be limited.
                </p>
            ) : null}

            <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                {Object.entries(permissionGroups).map(
                    ([group, groupPermissions]) => (
                        <fieldset key={group}>
                            <legend className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {displayName(group)}
                            </legend>
                            <div className="space-y-2">
                                {groupPermissions.map((permission) => {
                                    const checked =
                                        locked || selected.includes(permission);

                                    return (
                                        <label
                                            key={permission}
                                            className="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50"
                                        >
                                            <input
                                                type="checkbox"
                                                disabled={
                                                    !canManage || locked || isSaving
                                                }
                                                checked={checked}
                                                onChange={(event) =>
                                                    setSelected((current) =>
                                                        event.target.checked
                                                            ? [
                                                                  ...current,
                                                                  permission,
                                                              ]
                                                            : current.filter(
                                                                  (item) =>
                                                                      item !==
                                                                      permission,
                                                              ),
                                                    )
                                                }
                                                className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            {displayName(
                                                permission.split(".")[1] ??
                                                    permission,
                                            )}
                                        </label>
                                    );
                                })}
                            </div>
                        </fieldset>
                    ),
                )}
            </div>

            {canManage && !locked ? (
                <div className="flex justify-end border-t border-slate-200 pt-4">
                    <button
                        type="button"
                        onClick={() => void onSave(selected)}
                        disabled={isSaving}
                        className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save size={17} />
                        {isSaving ? "Saving..." : "Save permissions"}
                    </button>
                </div>
            ) : null}
        </FormSection>
    );
}
