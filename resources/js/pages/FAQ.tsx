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
        trigger: "What payment methods do you accept?",
        content:
            "We accept major payment methods available through our checkout. Available options may vary depending on your location and the payment provider.",
    },
    {
        value: "item-2",
        trigger: "How long does shipping take?",
        content:
            "Shipping times depend on your location and the delivery service selected during checkout. Estimated delivery information will be provided when you place your order.",
    },
    {
        value: "item-3",
        trigger: "Can I track my order?",
        content:
            "Yes. Once your order has been shipped, you will receive tracking information when it is available. You can use the tracking number to follow your package's delivery status.",
    },
    {
        value: "item-4",
        trigger: "Can I cancel my order?",
        content:
            "Orders can only be cancelled before they have been processed for shipment. If you need to cancel an order, contact us as soon as possible with your order details.",
    },
    {
        value: "item-5",
        trigger: "Can I return or exchange an item?",
        content:
            "Eligible products can be returned or exchanged according to our return policy. Items generally need to be unused and in their original condition and packaging. Contact us before sending an item back.",
    },
    {
        value: "item-6",
        trigger: "What if my order arrives damaged?",
        content:
            "If your order arrives damaged, contact us as soon as possible and provide your order number along with clear photos of the damaged packaging and product. We will review the issue and help determine the appropriate solution.",
    },
    {
        value: "item-7",
        trigger: "Are your gaming products covered by warranty?",
        content:
            "Warranty coverage depends on the product and manufacturer. Please check the product information or contact us if you need help determining the warranty coverage for a specific item.",
    },
    {
        value: "item-8",
        trigger: "How can I contact Checkpoint Store?",
        content:
            "You can contact Checkpoint Store through the contact information provided on our website. For order-related questions, include your order number so we can assist you more efficiently.",
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
