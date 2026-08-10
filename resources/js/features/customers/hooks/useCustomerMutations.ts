import { useMutation, useQueryClient } from "@tanstack/react-query";

import { customerApi } from "@/features/customers/api/customer.api";
import { customerKeys } from "@/features/customers/api/customer.keys";
import type {
    CustomerNote,
    CustomerStatus,
    CustomerUpdateValues,
} from "@/features/customers/types/customer.types";

function useInvalidateCustomer(customerId: number) {
    const queryClient = useQueryClient();
    return async () => {
        await Promise.all([
            queryClient.invalidateQueries({ queryKey: customerKeys.detail(customerId) }),
            queryClient.invalidateQueries({ queryKey: customerKeys.lists() }),
        ]);
    };
}

export function useUpdateCustomer(customerId: number) {
    const invalidate = useInvalidateCustomer(customerId);
    return useMutation({ mutationFn: (values: CustomerUpdateValues) => customerApi.update(customerId, values), onSuccess: invalidate });
}

export function useUpdateCustomerStatus(customerId: number) {
    const invalidate = useInvalidateCustomer(customerId);
    return useMutation({ mutationFn: (status: CustomerStatus) => customerApi.updateStatus(customerId, status), onSuccess: invalidate });
}

export function useCreateCustomerNote(customerId: number) {
    const invalidate = useInvalidateCustomer(customerId);
    return useMutation({ mutationFn: ({ note, isPinned }: { note: string; isPinned: boolean }) => customerApi.createNote(customerId, note, isPinned), onSuccess: invalidate });
}

export function useUpdateCustomerNote(customerId: number) {
    const invalidate = useInvalidateCustomer(customerId);
    return useMutation({ mutationFn: (note: CustomerNote) => customerApi.updateNote(customerId, note), onSuccess: invalidate });
}

export function useDeleteCustomerNote(customerId: number) {
    const invalidate = useInvalidateCustomer(customerId);
    return useMutation({ mutationFn: (noteId: number) => customerApi.deleteNote(customerId, noteId), onSuccess: invalidate });
}
