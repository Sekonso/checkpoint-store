import Footer from "@/components/blocks/Footer";
import Header from "@/components/blocks/Header";
import { ToastLoader } from "@/components/toast-loader";

type MainLayoutProps = {
    children: React.ReactNode;
};

export default function MainLayout({ children }: MainLayoutProps) {
    return (
        <>
            {/* Notification */}
            <ToastLoader />

            <Header />
            <main>{children}</main>
            <Footer />
        </>
    );
}
