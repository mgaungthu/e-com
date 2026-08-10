import { useState } from "react";

import type { Customer, CustomerStatus } from "@/features/customers/types/customer.types";
import {
    useCreateCustomerNote,
    useDeleteCustomerNote,
    useUpdateCustomer,
    useUpdateCustomerNote,
    useUpdateCustomerStatus,
} from "@/features/customers/hooks/useCustomerMutations";
import { useAuthStore } from "@/store/authStore";

export function CustomerManagementPanel({ customer }: { customer: Customer }) {
    const permissions = useAuthStore((state) => state.user?.permissions ?? []);
    const canUpdate = permissions.includes("customers.update");
    const canBlock = permissions.includes("customers.block");
    const profile = customer.customer_profile ?? customer.customerProfile;
    const [values, setValues] = useState({
        first_name: customer.first_name ?? "",
        last_name: customer.last_name ?? "",
        display_name: customer.display_name ?? "",
        phone: customer.phone ?? "",
        admin_note: profile?.admin_note ?? "",
    });
    const [noteText, setNoteText] = useState("");
    const [notePinned, setNotePinned] = useState(false);
    const updateCustomer = useUpdateCustomer(customer.id);
    const updateStatus = useUpdateCustomerStatus(customer.id);
    const createNote = useCreateCustomerNote(customer.id);
    const updateNote = useUpdateCustomerNote(customer.id);
    const deleteNote = useDeleteCustomerNote(customer.id);

    return (
        <div className="space-y-6">
            {canUpdate ? (
                <section className="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 className="font-bold text-slate-900">Manage customer</h2>
                    <div className="mt-4 grid gap-3 sm:grid-cols-2">
                        {(["first_name", "last_name", "display_name", "phone"] as const).map((field) => (
                            <input key={field} value={values[field]} placeholder={field.replace("_", " ")} onChange={(event) => setValues((current) => ({ ...current, [field]: event.target.value }))} className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm" />
                        ))}
                    </div>
                    <textarea value={values.admin_note} onChange={(event) => setValues((current) => ({ ...current, admin_note: event.target.value }))} rows={3} placeholder="Admin note" className="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    <button type="button" disabled={updateCustomer.isPending} onClick={() => updateCustomer.mutate(values)} className="mt-3 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50">Save customer</button>
                </section>
            ) : null}

            {canBlock ? (
                <section className="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 className="font-bold text-slate-900">Account status</h2>
                    <select value={customer.status} onChange={(event) => updateStatus.mutate(event.target.value as CustomerStatus)} className="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                        {['active', 'inactive', 'blocked', 'pending'].map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
                </section>
            ) : null}

            {canUpdate ? (
                <section className="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 className="font-bold text-slate-900">Manage notes</h2>
                    <textarea value={noteText} onChange={(event) => setNoteText(event.target.value)} rows={3} placeholder="Add an internal note" className="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    <label className="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" checked={notePinned} onChange={(event) => setNotePinned(event.target.checked)} /> Pin note</label>
                    <button type="button" disabled={!noteText.trim() || createNote.isPending} onClick={async () => { await createNote.mutateAsync({ note: noteText, isPinned: notePinned }); setNoteText(""); setNotePinned(false); }} className="mt-3 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50">Add note</button>
                    <div className="mt-4 space-y-3">
                        {(customer.customer_notes ?? customer.customerNotes ?? []).map((note) => (
                            <div key={note.id} className="rounded-lg border border-slate-200 p-3">
                                <p className="whitespace-pre-wrap text-sm text-slate-700">{note.note}</p>
                                <div className="mt-2 flex gap-2">
                                    <button type="button" onClick={() => updateNote.mutate({ ...note, is_pinned: !note.is_pinned })} className="text-xs font-semibold text-amber-700">{note.is_pinned ? "Unpin" : "Pin"}</button>
                                    <button type="button" onClick={() => { const text = window.prompt("Edit note", note.note); if (text?.trim()) updateNote.mutate({ ...note, note: text.trim() }); }} className="text-xs font-semibold text-blue-700">Edit</button>
                                    <button type="button" onClick={() => { if (window.confirm("Delete this note?")) deleteNote.mutate(note.id); }} className="text-xs font-semibold text-red-700">Delete</button>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            ) : null}
        </div>
    );
}
