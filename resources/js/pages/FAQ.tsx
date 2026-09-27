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
        trigger: "How can I contact Checkpoint Store?",
        content:
            "You can contact Checkpoint Store through the contact information in the footer below. For order-related questions, include your order number so we can assist you more efficiently.",
    },
    {
        trigger: "Are Checkpoint Store products covered by warranty?",
        content:
            "Warranty coverage depends on the product and manufacturer. Please check the product information or contact us if you need help determining the warranty coverage for a specific item.",
    },
    {
        trigger: "How do i purchase the products in Checkpoint Store?",
        content:
            "Sign your account then go the store page. Select your desired product and add it to the cart. Then visit the cart, do checkout, and confirm your payments. We will contact you upon successful purchase",
    },
    {
        trigger: "How do the payment works after i checkout?",
        content:
            "After checkout, you will be redirected to the invoice page or you can visit the page yourself through purchase history. There you can start initiate your payments, then payment button will show up. After clicking the button, you can choose the payment method then proceed to pay. After payment, make sure to refresh your status to see if your payment is succesfull or not",
    },
    {
        trigger: "How can i change my payment method?",
        content:
            "If you are already choosing a certain payment method and would like to change it. You can click the 'reset' button to reset your payment attempt. There you can choose your payment method again.",
    },
    {
        trigger: "Why is my ongoing/pending transactions gone?",
        content:
            "If you can't see you purchase history, that means your transaction are considered cancelled due to expiration date",
    },
    {
        trigger: "How can i track my order?",
        content:
            "After successful purchase. You can track your shipping status of your order in purchasing history. Will will also notify your order tracking through your email or phone, make sure to include valid contact information in your profile.",
    },
    {
        trigger: "What if my order arrives damaged?",
        content:
            "If your order arrives damaged, contact us as soon as possible and provide your order number along with clear photos of the damaged packaging and product. We will review the issue and help determine the appropriate solution.",
    },
    {
        trigger: "Can I cancel my order?",
        content:
            "If you haven't paid your order, you can cancel your order by visiting purchasing history. If your order is already paid, you can contact us for cancellation.",
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
                        <Accordion defaultValue={["item-0"]}>
                            {items.map((item, idx) => (
                                <AccordionItem key={idx} value={`item-${idx}`}>
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
