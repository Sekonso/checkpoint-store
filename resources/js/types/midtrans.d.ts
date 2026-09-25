interface SnapPayOptions {
    onSuccess?: (result: unknown) => void;
    onPending?: (result: unknown) => void;
    onError?: (result: unknown) => void;
    onClose?: () => void;
}

interface MidtransSnap {
    pay(token: string, options?: SnapPayOptions): void;
}

declare global {
    interface Window {
        snap?: MidtransSnap;
    }
}

export {};