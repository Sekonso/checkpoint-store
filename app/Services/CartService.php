<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }

    public function addItem(User $user, int $productId): void
    {
        $this->getOrCreateCart($user)->items()->firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 1],
        );
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        if ($quantity > $cartItem->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity exceeds available stock.',
            ]);
        }

        $cartItem->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function clearCart(User $user): void
    {
        $this->getOrCreateCart($user)->items()->delete();
    }
}
