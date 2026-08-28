import Footer from "@/components/blocks/Footer";
import HeaderError from "@/components/blocks/header-error";

type MainLayoutProps = {
    children: React.ReactNode;
};

export default function ErrorLayout({ children }: MainLayoutProps) {
    return (
        <>
            <HeaderError />
            <main>{children}</main>
            <Footer />
        </>
    );
}
