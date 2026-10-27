<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class CartService
{
    /** Return the user's cart, creating it when one does not exist. */
    public function getOrCreateCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }

    /**
     * Add one unit of a product to the user's cart.
     *
     * Only displayed products that are in stock can be added. Adding a
     * product that is already in the cart increases its quantity by one,
     * as long as the requested quantity does not exceed available stock.
     *
     * @throws ValidationException
     */
    public function addItem(User $user, int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        if (! $product->in_display || $product->stock < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not available.',
            ]);
        }

        $cartItem = $this->getOrCreateCart($user)
            ->items()
            ->firstOrNew(['product_id' => $product->id]);

        $quantity = ($cartItem->quantity ?? 0) + 1;

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity exceeds available stock.',
            ]);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();
    }

    /**
     * Set a cart item's quantity after checking it against available stock.
     *
     * @throws ValidationException When the requested quantity exceeds stock.
     */
    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        if ($quantity > $cartItem->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity exceeds available stock.',
            ]);
        }

        $cartItem->update(['quantity' => $quantity]);
    }

    /** Remove a single item from its cart. */
    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    /** Delete all items from the user's cart. */
    public function clearCart(User $user): void
    {
        $this->getOrCreateCart($user)->items()->delete();
    }
}
