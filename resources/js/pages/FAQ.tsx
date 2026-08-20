import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/components/ui/accordion";
import MainLayout from "@/layouts/MainLayout";
import { Head } from "@inertiajs/react";

const items = [
    {
        value: "item-1",
        trigger: "How do I reset my password?",
        content:
            "Click on 'Forgot Password' on the login page, enter your email address, and we'll send you a link to reset your password. The link will expire in 24 hours.",
    },
    {
        value: "item-2",
        trigger: "Can I change my subscription plan?",
        content:
            "Yes, you can upgrade or downgrade your plan at any time from your account settings. Changes will be reflected in your next billing cycle.",
    },
    {
        value: "item-3",
        trigger: "What payment methods do you accept?",
        content:
            "We accept all major credit cards, PayPal, and bank transfers. All payments are processed securely through our payment partners.",
    },
];

export default function FAQ() {
    return (
        <>
            <Head title="FAQ" />

            <MainLayout>
                <div className="wrapper mb-20">
                    <h1 className="font-heading my-8 text-center text-4xl font-bold uppercase">
                        FAQ
                    </h1>
                    <div className="mx-auto max-w-150">
                        <Accordion defaultValue={["item-1"]}>
                            {items.map((item) => (
                                <AccordionItem
                                    key={item.value}
                                    value={item.value}
                                >
                                    <AccordionTrigger className="font-semibold hover:no-underline sm:text-lg">
                                        {item.trigger}
                                    </AccordionTrigger>
                                    <AccordionContent>
                                        {item.content}
                                    </AccordionContent>
                                </AccordionItem>
                            ))}
                        </Accordion>
                    </div>
                </div>
            </MainLayout>
        </>
    );
}
