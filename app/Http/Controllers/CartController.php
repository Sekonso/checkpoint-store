<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart()->firstOrCreate([]);
        $cart->load([
            'items' => fn($query) => $query->latest('created_at'),
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
            // Validation
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            // Add product to cart
            $cart = Auth::user()->cart()->firstOrCreate([]);
            $cart->items()->firstOrCreate(
                ['product_id' => $validated['product_id']],
                ['quantity' => 1],
            );

            // Success
            return back();
        } catch (\Throwable $e) {
            report($e);

            if (app()->environment(['local', 'development'])) {
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

        if ($validated['quantity'] > $cartItem->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity exceeds available stock.',
            ]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cart quantity updated.',
        ]);
    }

    public function destroyItem(CartItem $cartItem)
    {
        $this->ensureOwnership($cartItem);
        $cartItem->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Product removed from cart.',
        ]);
    }

    public function clear()
    {
        Auth::user()->cart()->firstOrCreate([])->items()->delete();

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
