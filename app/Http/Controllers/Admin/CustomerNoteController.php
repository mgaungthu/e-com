<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\CustomerNoteRequest;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class CustomerNoteController extends Controller
{
    public function store(CustomerNoteRequest $request, User $customer): JsonResponse
    {
        abort_unless($customer->hasRole('customer'), 404);
        $note = $customer->customerNotes()->create([
            'created_by' => $request->user()->id,
            'note' => $request->validated('note'),
            'is_pinned' => $request->boolean('is_pinned'),
        ])->load('creator:id,name,first_name,last_name,display_name');

        return response()->json(['success' => true, 'message' => 'Customer note created successfully.', 'data' => ['note' => $note]], 201);
    }

    public function update(CustomerNoteRequest $request, User $customer, CustomerNote $note): JsonResponse
    {
        $this->ensureBelongsToCustomer($note, $customer);
        $note->update([
            'note' => $request->validated('note'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return response()->json(['success' => true, 'message' => 'Customer note updated successfully.', 'data' => ['note' => $note->load('creator:id,name,first_name,last_name,display_name')]]);
    }

    public function destroy(CustomerNoteRequest $request, User $customer, CustomerNote $note): JsonResponse
    {
        $this->ensureBelongsToCustomer($note, $customer);
        $note->delete();

        return response()->json(['success' => true, 'message' => 'Customer note deleted successfully.']);
    }

    private function ensureBelongsToCustomer(CustomerNote $note, User $customer): void
    {
        abort_unless($customer->hasRole('customer') && $note->user_id === $customer->id, 404);
    }
}
