type MainLayoutProps = {
    children: React.ReactNode;
};

export default function MainLayout({ children }: MainLayoutProps) {
    return (
        <>
            <main className="wrapper flex items-center justify-center h-dvh">{children}</main>
        </>
    );
}
