<?php

class CartModel
{
    public static function getCart()
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function add($product, $quantity)
    {
        $cart = self::getCart();
        $productId = $product['id'];

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product' => $product,
                'quantity' => $quantity,
            ];
        }

        $_SESSION['cart'] = $cart;
    }

    public static function clearCart()
    {
        unset($_SESSION['cart']);
    }
}
