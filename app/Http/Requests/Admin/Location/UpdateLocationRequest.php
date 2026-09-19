<?php

namespace App\Http\Requests\Admin\Location;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'locations.update',
        ) ?? false;
    }

    public function rules(): array
    {
        $location = $this->route(
            'location',
        );

        return [
            'parent_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'locations',
                    'id',
                ),

                Rule::notIn([
                    $location?->id,
                ]),
            ],

            'name_en' => [
                'required',
                'string',
                'max:150',
            ],

            'name_mm' => [
                'nullable',
                'string',
                'max:150',
            ],

            'type' => [
                'required',
                'string',
                'max:30',
            ],

            'shipping_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (
                Validator $validator,
            ): void {
                if (
                    $validator
                        ->errors()
                        ->isNotEmpty()
                ) {
                    return;
                }

                /** @var Location|null $location */
                $location = $this->route(
                    'location',
                );

                if (! $location) {
                    return;
                }

                $parentId = $this->input(
                    'parent_id',
                );

                if ($parentId === null) {
                    return;
                }

                $parentId = (int) $parentId;

                /*
                 * Self parent protection.
                 */
                if (
                    $parentId ===
                    (int) $location->id
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'parent_id',
                            'A location cannot be its own parent.',
                        );

                    return;
                }

                /*
                 * Descendant protection.
                 *
                 * A child location cannot become
                 * the parent of its own ancestor.
                 */
                if (
                    $this->isDescendant(
                        $location,
                        $parentId,
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'parent_id',
                            'A child location cannot be selected as the parent.',
                        );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => $this->filled(
                'parent_id',
            )
                ? $this->input('parent_id')
                : null,

            'name_mm' => $this->filled(
                'name_mm',
            )
                ? $this->input('name_mm')
                : null,

            'shipping_fee' => $this->filled(
                'shipping_fee',
            )
                ? $this->input('shipping_fee')
                : null,

            'is_active' => $this->boolean(
                'is_active',
            ),

            'sort_order' => $this->input(
                'sort_order',
                0,
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' =>
                'The selected parent location is invalid.',

            'parent_id.not_in' =>
                'A location cannot be its own parent.',

            'name_en.required' =>
                'The English location name field is required.',

            'type.required' =>
                'The location type field is required.',

            'shipping_fee.numeric' =>
                'The shipping fee must be a valid number.',

            'shipping_fee.min' =>
                'The shipping fee cannot be negative.',
        ];
    }

    private function isDescendant(
        Location $location,
        int $parentId,
    ): bool {
        $current = Location::query()
            ->select([
                'id',
                'parent_id',
            ])
            ->find($parentId);

        while ($current !== null) {
            if (
                (int) $current->id ===
                (int) $location->id
            ) {
                return true;
            }

            if (
                $current->parent_id === null
            ) {
                break;
            }

            $current = Location::query()
                ->select([
                    'id',
                    'parent_id',
                ])
                ->find(
                    $current->parent_id,
                );
        }

        return false;
    }
}