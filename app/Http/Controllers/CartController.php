<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        $cart = $this->cart->getOrCreateCart(Auth::user());
        $cart->load([
            'items' => fn ($query) => $query->latest('created_at'),
            'items.product.images',
            'items.product.category',
        ]);

        return Inertia::render('Cart/index', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $this->cart->addItem(Auth::user(), (int) $validated['product_id']);

            return back();
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->withErrors([
                'cart' => 'error',
            ]);
        }
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->ensureOwnership($cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cart->updateQuantity($cartItem, (int) $validated['quantity']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cart quantity updated.',
        ]);
    }

    public function destroyItem(CartItem $cartItem)
    {
        $this->ensureOwnership($cartItem);
        $this->cart->removeItem($cartItem);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Product removed from cart.',
        ]);
    }

    public function clear()
    {
        $this->cart->clearCart(Auth::user());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cart cleared.',
        ]);
    }

    private function ensureOwnership(CartItem $cartItem): void
    {
        abort_unless($cartItem->cart->user_id === Auth::id(), 404);
    }
}
