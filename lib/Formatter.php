<?php

/**
 * Server-side data formatting utilities.
 * Mirrors the formatting helpers found in assets/js/app.js so that the same
 * display rules are available when generating server-rendered output or exports.
 */
class Formatter
{
    // -------------------------------------------------------------------------
    // Currency & numbers  (mirrors IMS.formatCurrency / IMS.formatNumber in app.js)
    // -------------------------------------------------------------------------

    /** Format a number as Malaysian Ringgit, e.g. "RM 1,234.56" */
    public static function currency(float|int|string $amount, string $symbol = 'RM'): string
    {
        $value = (float)$amount;
        return $symbol . ' ' . number_format($value, 2, '.', ',');
    }

    /** Format a plain number with thousand-separators, e.g. "1,234" or "1,234.56" */
    public static function number(float|int|string $value, int $decimals = 0): string
    {
        return number_format((float)$value, $decimals, '.', ',');
    }

    /** Format a percentage, e.g. "12.50%" */
    public static function percent(float|int $value, int $decimals = 2): string
    {
        return number_format($value, $decimals) . '%';
    }

    // -------------------------------------------------------------------------
    // Dates & times
    // -------------------------------------------------------------------------

    /** Format a DB datetime string for display, e.g. "20 Apr 2026 14:30" */
    public static function datetime(string $datetime, string $format = 'd M Y H:i'): string
    {
        if (!$datetime) return '-';
        $ts = strtotime($datetime);
        return $ts !== false ? date($format, $ts) : '-';
    }

    /** Format a DB date string, e.g. "20 Apr 2026" */
    public static function date(string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '-';
        $ts = strtotime($date);
        return $ts !== false ? date($format, $ts) : '-';
    }

    /** Return a relative time string, e.g. "3 hours ago" or "just now" */
    public static function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60)         return 'just now';
        if ($diff < 3600)       return (int)($diff / 60) . ' minute' . ($diff < 120 ? '' : 's') . ' ago';
        if ($diff < 86400)      return (int)($diff / 3600) . ' hour' . ($diff < 7200 ? '' : 's') . ' ago';
        if ($diff < 2592000)    return (int)($diff / 86400) . ' day' . ($diff < 172800 ? '' : 's') . ' ago';
        if ($diff < 31536000)   return (int)($diff / 2592000) . ' month' . ($diff < 5184000 ? '' : 's') . ' ago';
        return (int)($diff / 31536000) . ' year' . ($diff < 63072000 ? '' : 's') . ' ago';
    }

    // -------------------------------------------------------------------------
    // Stock status  (mirrors IMS.stockBadge in app.js)
    // -------------------------------------------------------------------------

    /** Return stock status label: 'out_of_stock' | 'low_stock' | 'in_stock' */
    public static function stockStatus(int $quantity, int $threshold = 10): string
    {
        if ($quantity <= 0)          return 'out_of_stock';
        if ($quantity <= $threshold) return 'low_stock';
        return 'in_stock';
    }

    /** Return a human-readable stock status label */
    public static function stockLabel(int $quantity, int $threshold = 10): string
    {
        return match (self::stockStatus($quantity, $threshold)) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            default        => 'In Stock',
        };
    }

    // -------------------------------------------------------------------------
    // Strings
    // -------------------------------------------------------------------------

    /** Truncate a string to a max length, appending an ellipsis if needed */
    public static function truncate(string $text, int $maxLength = 50, string $ellipsis = '...'): string
    {
        if (mb_strlen($text) <= $maxLength) return $text;
        return mb_substr($text, 0, $maxLength - mb_strlen($ellipsis)) . $ellipsis;
    }

    /** Convert a snake_case or camelCase string to Title Case */
    public static function titleCase(string $text): string
    {
        $words = preg_split('/[_\s]+|(?<=[a-z])(?=[A-Z])/', $text);
        return implode(' ', array_map('ucfirst', array_map('strtolower', $words)));
    }

    /** Sanitize a string for safe HTML output */
    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Strip non-alphanumeric characters and return a safe slug */
    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        return preg_replace('/[\s-]+/', '-', $text);
    }

    // -------------------------------------------------------------------------
    // File sizes
    // -------------------------------------------------------------------------

    /** Format bytes into a human-readable file size string, e.g. "1.2 MB" */
    public static function fileSize(int $bytes): string
    {
        if ($bytes < 1024)        return $bytes . ' B';
        if ($bytes < 1048576)     return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824)  return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 1) . ' GB';
    }

    // -------------------------------------------------------------------------
    // Arrays / data
    // -------------------------------------------------------------------------

    /**
     * Format a product record for API output, applying escaping and formatting.
     * Mirrors the normalisation done in the JS DataTable column renderers.
     */
    public static function productRecord(array $row): array
    {
        return [
            'product_id'          => (int)$row['product_id'],
            'product_name'        => self::escape($row['product_name'] ?? ''),
            'description'         => self::escape($row['description'] ?? ''),
            'price'               => (float)$row['price'],
            'price_formatted'     => self::currency($row['price']),
            'quantity'            => (int)$row['quantity'],
            'low_stock_threshold' => (int)($row['low_stock_threshold'] ?? 10),
            'stock_status'        => self::stockStatus((int)$row['quantity'], (int)($row['low_stock_threshold'] ?? 10)),
            'stock_label'         => self::stockLabel((int)$row['quantity'], (int)($row['low_stock_threshold'] ?? 10)),
            'category_id'         => (int)$row['category_id'],
            'category_name'       => self::escape($row['category_name'] ?? ''),
            'supplier_id'         => (int)$row['supplier_id'],
            'supplier_name'       => self::escape($row['supplier_name'] ?? ''),
            'image_url'           => $row['image_url'] ?? null,
            'created_at'          => $row['created_at'] ?? null,
            'created_at_formatted'=> isset($row['created_at']) ? self::datetime($row['created_at']) : '-',
        ];
    }

    /**
     * Format a transaction record for API output.
     */
    public static function transactionRecord(array $row): array
    {
        return [
            'transaction_id'       => (int)$row['transaction_id'],
            'product_id'           => (int)$row['product_id'],
            'product_name'         => self::escape($row['product_name'] ?? ''),
            'transaction_type'     => self::escape($row['transaction_type'] ?? ''),
            'transaction_type_id'  => (int)$row['transaction_type_id'],
            'quantity'             => (int)$row['quantity'],
            'unit_price'           => (float)$row['unit_price'],
            'unit_price_formatted' => self::currency($row['unit_price']),
            'total_amount'         => (float)$row['total_amount'],
            'total_formatted'      => self::currency($row['total_amount']),
            'transaction_date'     => $row['transaction_date'] ?? null,
            'date_formatted'       => isset($row['transaction_date']) ? self::date($row['transaction_date']) : '-',
            'notes'                => self::escape($row['notes'] ?? ''),
            'created_at'           => $row['created_at'] ?? null,
        ];
    }

    /**
     * Format a supplier record for API output.
     */
    public static function supplierRecord(array $row): array
    {
        return [
            'supplier_id'    => (int)$row['supplier_id'],
            'supplier_name'  => self::escape($row['supplier_name'] ?? ''),
            'contact_person' => self::escape($row['contact_person'] ?? ''),
            'email'          => self::escape($row['email'] ?? ''),
            'phone'          => self::escape($row['phone'] ?? ''),
            'address'        => self::escape($row['address'] ?? ''),
            'product_count'  => (int)($row['product_count'] ?? 0),
            'created_at'     => $row['created_at'] ?? null,
            'created_at_formatted' => isset($row['created_at']) ? self::datetime($row['created_at']) : '-',
        ];
    }
}
