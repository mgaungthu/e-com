import { type FormEvent, useEffect, useState } from "react";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import axios from "axios";
import { CheckCircle2, Package, Settings2, ShoppingCart, Store } from "lucide-react";

import PageHeader from "@/components/PageHeader";
import {
    FormActions,
    FormErrorAlert,
    FormField,
    FormSection,
    NumberInput,
    TextInput,
} from "@/components/forms";
import {
    settingApi,
    type Settings,
} from "@/features/settings/api/setting.api";

type ValidationResponse = {
    message?: string;
    errors?: Record<string, string[]>;
};

const defaults: Settings = {
    store_name: "",
    support_email: "",
    support_phone: "",
    currency: "MMK",
    timezone: "Asia/Yangon",
    order_prefix: "ORD",
    default_low_stock_threshold: 5,
    tax_rate: 0,
    shipping_fee: 0,
};

export default function SettingsPage() {
    const queryClient = useQueryClient();
    const query = useQuery({
        queryKey: ["settings"],
        queryFn: settingApi.get,
    });
    const [values, setValues] = useState<Settings>(defaults);
    const [validationErrors, setValidationErrors] = useState<
        Record<string, string[]>
    >({});
    const [formError, setFormError] = useState<string | null>(null);

    const mutation = useMutation({
        mutationFn: settingApi.update,
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["settings"] });
        },
    });

    useEffect(() => {
        if (query.data?.data) {
            setValues(query.data.data);
        }
    }, [query.data]);

    const update = <K extends keyof Settings>(
        key: K,
        value: Settings[K],
    ) => {
        setValues((current) => ({ ...current, [key]: value }));
        setValidationErrors((current) => ({ ...current, [key]: [] }));
        setFormError(null);
    };

    const fieldError = (key: keyof Settings) =>
        validationErrors[key]?.[0] ?? null;

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setValidationErrors({});
        setFormError(null);

        try {
            await mutation.mutateAsync(values);
        } catch (error) {
            if (axios.isAxiosError<ValidationResponse>(error)) {
                setValidationErrors(error.response?.data.errors ?? {});
                setFormError(
                    error.response?.data.message ??
                        "Unable to save settings. Please check the form.",
                );
                return;
            }

            setFormError("Unable to save settings. Please try again.");
        }
    }

    if (query.isLoading) {
        return (
            <div className="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                Loading settings...
            </div>
        );
    }

    if (query.isError) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-10 text-center">
                <p className="text-sm font-medium text-red-700">
                    Unable to load settings.
                </p>
                <button
                    type="button"
                    onClick={() => void query.refetch()}
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
                title="Settings"
                description="Configure your store information, commerce rules, and inventory defaults."
            />

            <form onSubmit={handleSubmit} className="space-y-6" noValidate>
                <FormErrorAlert message={formError} />

                {mutation.isSuccess && !formError ? (
                    <div className="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        <CheckCircle2 size={18} />
                        Settings saved successfully.
                    </div>
                ) : null}

                <FormSection
                    title="General information"
                    description="Store identity and customer support contact details."
                    icon={<Store size={20} />}
                    contentClassName="grid gap-5 p-5 md:grid-cols-2"
                >
                    <FormField
                        label="Store name"
                        htmlFor="store_name"
                        required
                        error={fieldError("store_name")}
                    >
                        <TextInput
                            id="store_name"
                            value={values.store_name}
                            onChange={(event) =>
                                update("store_name", event.target.value)
                            }
                            disabled={mutation.isPending}
                            placeholder="Myanmar Store"
                            error={fieldError("store_name")}
                        />
                    </FormField>

                    <FormField
                        label="Timezone"
                        htmlFor="timezone"
                        required
                        helperText="Use a valid timezone such as Asia/Yangon."
                        error={fieldError("timezone")}
                    >
                        <TextInput
                            id="timezone"
                            value={values.timezone}
                            onChange={(event) =>
                                update("timezone", event.target.value)
                            }
                            disabled={mutation.isPending}
                            placeholder="Asia/Yangon"
                            error={fieldError("timezone")}
                        />
                    </FormField>

                    <FormField
                        label="Support email"
                        htmlFor="support_email"
                        error={fieldError("support_email")}
                    >
                        <TextInput
                            id="support_email"
                            type="email"
                            value={values.support_email}
                            onChange={(event) =>
                                update("support_email", event.target.value)
                            }
                            disabled={mutation.isPending}
                            placeholder="support@example.com"
                            error={fieldError("support_email")}
                        />
                    </FormField>

                    <FormField
                        label="Support phone"
                        htmlFor="support_phone"
                        error={fieldError("support_phone")}
                    >
                        <TextInput
                            id="support_phone"
                            type="tel"
                            value={values.support_phone}
                            onChange={(event) =>
                                update("support_phone", event.target.value)
                            }
                            disabled={mutation.isPending}
                            placeholder="09 123 456 789"
                            error={fieldError("support_phone")}
                        />
                    </FormField>
                </FormSection>

                <FormSection
                    title="Commerce"
                    description="Order numbering, tax, currency, and delivery charges."
                    icon={<ShoppingCart size={20} />}
                    contentClassName="grid gap-5 p-5 md:grid-cols-2"
                >
                    <FormField
                        label="Currency"
                        htmlFor="currency"
                        helperText="All prices in the admin are displayed in Myanmar Kyat."
                        error={fieldError("currency")}
                    >
                        <TextInput
                            id="currency"
                            value="MMK"
                            disabled
                            error={fieldError("currency")}
                        />
                    </FormField>

                    <FormField
                        label="Order prefix"
                        htmlFor="order_prefix"
                        required
                        helperText="Uppercase letters and numbers only."
                        error={fieldError("order_prefix")}
                    >
                        <TextInput
                            id="order_prefix"
                            value={values.order_prefix}
                            onChange={(event) =>
                                update(
                                    "order_prefix",
                                    event.target.value.toUpperCase(),
                                )
                            }
                            disabled={mutation.isPending}
                            maxLength={10}
                            placeholder="ORD"
                            error={fieldError("order_prefix")}
                        />
                    </FormField>

                    <FormField
                        label="Tax rate"
                        htmlFor="tax_rate"
                        required
                        error={fieldError("tax_rate")}
                    >
                        <NumberInput
                            id="tax_rate"
                            min="0"
                            max="100"
                            step="0.01"
                            suffix="%"
                            value={values.tax_rate}
                            onChange={(event) =>
                                update("tax_rate", Number(event.target.value))
                            }
                            disabled={mutation.isPending}
                            error={fieldError("tax_rate")}
                        />
                    </FormField>

                    <FormField
                        label="Shipping fee"
                        htmlFor="shipping_fee"
                        required
                        error={fieldError("shipping_fee")}
                    >
                        <NumberInput
                            id="shipping_fee"
                            min="0"
                            step="1"
                            suffix="MMK"
                            value={values.shipping_fee}
                            onChange={(event) =>
                                update(
                                    "shipping_fee",
                                    Number(event.target.value),
                                )
                            }
                            disabled={mutation.isPending}
                            error={fieldError("shipping_fee")}
                            className="pr-16"
                        />
                    </FormField>
                </FormSection>

                <FormSection
                    title="Inventory"
                    description="Default stock level used to identify low-stock products."
                    icon={<Package size={20} />}
                >
                    <FormField
                        label="Default low stock threshold"
                        htmlFor="default_low_stock_threshold"
                        required
                        helperText="Products at or below this quantity will be marked as low stock."
                        error={fieldError("default_low_stock_threshold")}
                        className="max-w-xl"
                    >
                        <NumberInput
                            id="default_low_stock_threshold"
                            min="0"
                            step="1"
                            value={values.default_low_stock_threshold}
                            onChange={(event) =>
                                update(
                                    "default_low_stock_threshold",
                                    Number(event.target.value),
                                )
                            }
                            disabled={mutation.isPending}
                            error={fieldError(
                                "default_low_stock_threshold",
                            )}
                        />
                    </FormField>
                </FormSection>

                <FormActions
                    submitLabel="Save settings"
                    submittingLabel="Saving settings..."
                    isSubmitting={mutation.isPending}
                    submitIcon={<Settings2 size={18} />}
                />
            </form>
        </div>
    );
}
