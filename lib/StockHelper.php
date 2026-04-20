<?php

/**
 * Stock-level calculation utilities.
 * Mirrors the stock-badge and stock-alert logic in assets/js/app.js and
 * centralises the stock-update arithmetic spread across db/transaction_db.php.
 */
class StockHelper
{
    // -------------------------------------------------------------------------
    // Stock status (mirrors IMS.stockBadge thresholds in app.js)
    // -------------------------------------------------------------------------

    const STATUS_OUT_OF_STOCK = 'out_of_stock';
    const STATUS_LOW_STOCK    = 'low_stock';
    const STATUS_IN_STOCK     = 'in_stock';

    /**
     * Return the stock status constant for a given quantity and threshold.
     */
    public static function status(int $quantity, int $threshold = 10): string
    {
        if ($quantity <= 0)          return self::STATUS_OUT_OF_STOCK;
        if ($quantity <= $threshold) return self::STATUS_LOW_STOCK;
        return self::STATUS_IN_STOCK;
    }

    /**
     * Return true if a product needs restocking.
     */
    public static function needsRestock(int $quantity, int $threshold = 10): bool
    {
        return $quantity <= $threshold;
    }

    /**
     * Return true if the product is completely out of stock.
     */
    public static function isOutOfStock(int $quantity): bool
    {
        return $quantity <= 0;
    }

    // -------------------------------------------------------------------------
    // Transaction stock arithmetic (mirrors logic in db/transaction_db.php)
    // -------------------------------------------------------------------------

    /**
     * Calculate the new stock quantity after a transaction.
     *
     * @param  int    $currentStock   Current product stock.
     * @param  int    $txnQuantity    Quantity in the new transaction.
     * @param  string $txnType        'sale' or 'purchase'.
     * @return int                    New stock level.
     * @throws \InvalidArgumentException  If the transaction type is unknown.
     * @throws \UnderflowException        If a sale would make stock negative.
     */
    public static function applyTransaction(
        int    $currentStock,
        int    $txnQuantity,
        string $txnType
    ): int {
        $type = strtolower(trim($txnType));

        if ($type === 'purchase') {
            return $currentStock + $txnQuantity;
        }

        if ($type === 'sale') {
            $newStock = $currentStock - $txnQuantity;
            if ($newStock < 0) {
                throw new \UnderflowException(
                    "Insufficient stock: only $currentStock unit(s) available, $txnQuantity requested"
                );
            }
            return $newStock;
        }

        throw new \InvalidArgumentException("Unknown transaction type: $txnType");
    }

    /**
     * Reverse a previously applied transaction (used when editing or deleting).
     * This is the inverse of applyTransaction.
     *
     * @param  int    $currentStock   Current product stock.
     * @param  int    $txnQuantity    Quantity of the transaction being reversed.
     * @param  string $txnType        'sale' or 'purchase'.
     * @return int                    Stock after reversal.
     */
    public static function reverseTransaction(
        int    $currentStock,
        int    $txnQuantity,
        string $txnType
    ): int {
        $type = strtolower(trim($txnType));

        // Reversing a sale puts stock back; reversing a purchase removes stock.
        if ($type === 'sale')     return $currentStock + $txnQuantity;
        if ($type === 'purchase') return max(0, $currentStock - $txnQuantity);

        throw new \InvalidArgumentException("Unknown transaction type: $txnType");
    }

    /**
     * Calculate the net stock change when editing a transaction:
     * reverse the old values, then apply the new ones.
     *
     * @param  int    $currentStock
     * @param  int    $oldQuantity
     * @param  string $oldType
     * @param  int    $newQuantity
     * @param  string $newType
     * @return int    Resulting stock level.
     */
    public static function editTransaction(
        int    $currentStock,
        int    $oldQuantity,
        string $oldType,
        int    $newQuantity,
        string $newType
    ): int {
        $stockAfterReversal = self::reverseTransaction($currentStock, $oldQuantity, $oldType);
        return self::applyTransaction($stockAfterReversal, $newQuantity, $newType);
    }

    // -------------------------------------------------------------------------
    // Reporting helpers
    // -------------------------------------------------------------------------

    /**
     * Given an array of product rows, return only those at or below threshold.
     *
     * @param  array[] $products  Each row must have 'quantity' and optionally 'low_stock_threshold'.
     * @return array[]
     */
    public static function filterLowStock(array $products): array
    {
        return array_values(array_filter($products, function (array $p) {
            $threshold = (int)($p['low_stock_threshold'] ?? 10);
            return (int)$p['quantity'] <= $threshold && (int)$p['quantity'] > 0;
        }));
    }

    /**
     * Return only out-of-stock products.
     *
     * @param  array[] $products
     * @return array[]
     */
    public static function filterOutOfStock(array $products): array
    {
        return array_values(array_filter($products, fn($p) => (int)$p['quantity'] <= 0));
    }

    /**
     * Compute a stock summary for a list of product rows.
     *
     * @param  array[] $products
     * @return array{total: int, in_stock: int, low_stock: int, out_of_stock: int}
     */
    public static function summary(array $products): array
    {
        $summary = ['total' => count($products), 'in_stock' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
        foreach ($products as $p) {
            $threshold = (int)($p['low_stock_threshold'] ?? 10);
            $qty       = (int)$p['quantity'];
            switch (self::status($qty, $threshold)) {
                case self::STATUS_OUT_OF_STOCK: $summary['out_of_stock']++; break;
                case self::STATUS_LOW_STOCK:    $summary['low_stock']++;    break;
                default:                        $summary['in_stock']++;     break;
            }
        }
        return $summary;
    }

    /**
     * Calculate total inventory value (sum of price × quantity).
     *
     * @param  array[] $products  Each row must have 'price' and 'quantity'.
     */
    public static function totalInventoryValue(array $products): float
    {
        return array_reduce($products, function (float $carry, array $p) {
            return $carry + ((float)$p['price'] * (int)$p['quantity']);
        }, 0.0);
    }

    /**
     * Calculate total transaction amount (sum of unit_price × quantity).
     *
     * @param  array[] $transactions  Each row must have 'unit_price' and 'quantity'.
     */
    public static function totalTransactionAmount(array $transactions): float
    {
        return array_reduce($transactions, function (float $carry, array $t) {
            return $carry + ((float)$t['unit_price'] * (int)$t['quantity']);
        }, 0.0);
    }

    /**
     * Return the number of units needed to reach a target stock level.
     * Returns 0 if the current quantity already meets or exceeds the target.
     */
    public static function restockQuantity(int $currentQuantity, int $targetQuantity): int
    {
        return max(0, $targetQuantity - $currentQuantity);
    }
}
