<?php

// Currency configuration
define('CURRENCY', 'FCFA');
define('CURRENCY_SYMBOL', 'FCFA');

function formatPrice($price)
{
    return number_format($price, 2, ',', ' ') . ' ' . CURRENCY_SYMBOL;
}

function formatCurrency($amount)
{
    return number_format($amount, 0, '', ' ') . ' ' . CURRENCY_SYMBOL;
}
