<?php

/**
 * Server-side input validation mirroring the JS validation logic in assets/js/pages/.
 * All methods return ['valid' => bool, 'errors' => string[]].
 */
class Validator
{
    // -------------------------------------------------------------------------
    // Generic helpers
    // -------------------------------------------------------------------------

    public static function required(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            return "$label is required";
        }
        return null;
    }

    public static function minLength(string $value, int $min, string $label): ?string
    {
        if (strlen(trim($value)) < $min) {
            return "$label must be at least $min characters";
        }
        return null;
    }

    public static function maxLength(string $value, int $max, string $label): ?string
    {
        if (strlen(trim($value)) > $max) {
            return "$label must not exceed $max characters";
        }
        return null;
    }

    public static function minValue(float|int $value, float|int $min, string $label): ?string
    {
        if ($value < $min) {
            return "$label must be at least $min";
        }
        return null;
    }

    public static function maxValue(float|int $value, float|int $max, string $label): ?string
    {
        if ($value > $max) {
            return "$label must not exceed $max";
        }
        return null;
    }

    public static function isNumeric(mixed $value, string $label): ?string
    {
        if (!is_numeric($value)) {
            return "$label must be a number";
        }
        return null;
    }

    public static function isInteger(mixed $value, string $label): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "$label must be a whole number";
        }
        return null;
    }

    public static function isPositive(float|int $value, string $label): ?string
    {
        if ($value <= 0) {
            return "$label must be greater than 0";
        }
        return null;
    }

    public static function isNonNegative(float|int $value, string $label): ?string
    {
        if ($value < 0) {
            return "$label cannot be negative";
        }
        return null;
    }

    public static function isEmail(string $value, string $label): ?string
    {
        if (!filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
            return "$label must be a valid email address";
        }
        return null;
    }

    public static function isPhone(string $value, string $label): ?string
    {
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return "$label must be a valid phone number";
        }
        return null;
    }

    public static function isUrl(string $value, string $label): ?string
    {
        if (!filter_var(trim($value), FILTER_VALIDATE_URL)) {
            return "$label must be a valid URL";
        }
        return null;
    }

    public static function inSet(mixed $value, array $allowed, string $label): ?string
    {
        if (!in_array($value, $allowed, true)) {
            return "$label must be one of: " . implode(', ', $allowed);
        }
        return null;
    }

    public static function isDate(string $value, string $label, string $format = 'Y-m-d'): ?string
    {
        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            return "$label must be a valid date ($format)";
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Entity validators
    // -------------------------------------------------------------------------

    /**
     * Validate product creation / update payload.
     * Mirrors the JS validation in assets/js/pages/products.js.
     */
    public static function product(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        $name     = trim($data['product_name'] ?? '');
        $price    = $data['price'] ?? null;
        $stock    = $data['quantity'] ?? null;
        $catId    = $data['category_id'] ?? null;
        $supplierId = $data['supplier_id'] ?? null;
        $lowStock = $data['low_stock_threshold'] ?? null;

        // Product name
        if ($err = self::required($name, 'Product name')) $errors[] = $err;
        elseif ($err = self::minLength($name, 2, 'Product name')) $errors[] = $err;
        elseif ($err = self::maxLength($name, 100, 'Product name')) $errors[] = $err;

        // Price
        if ($err = self::required($price, 'Price')) {
            $errors[] = $err;
        } else {
            if ($err = self::isNumeric($price, 'Price')) $errors[] = $err;
            elseif ($err = self::isNonNegative((float)$price, 'Price')) $errors[] = $err;
            elseif ($err = self::maxValue((float)$price, 999999.99, 'Price')) $errors[] = $err;
        }

        // Quantity / stock
        if ($err = self::required($stock, 'Quantity')) {
            $errors[] = $err;
        } else {
            if ($err = self::isInteger($stock, 'Quantity')) $errors[] = $err;
            elseif ($err = self::isNonNegative((int)$stock, 'Quantity')) $errors[] = $err;
        }

        // Category
        if ($err = self::required($catId, 'Category')) {
            $errors[] = $err;
        } elseif ($err = self::isInteger($catId, 'Category')) {
            $errors[] = $err;
        }

        // Supplier
        if ($err = self::required($supplierId, 'Supplier')) {
            $errors[] = $err;
        } elseif ($err = self::isInteger($supplierId, 'Supplier')) {
            $errors[] = $err;
        }

        // Low stock threshold (optional)
        if ($lowStock !== null && $lowStock !== '') {
            if ($err = self::isInteger($lowStock, 'Low stock threshold')) $errors[] = $err;
            elseif ($err = self::isNonNegative((int)$lowStock, 'Low stock threshold')) $errors[] = $err;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate supplier payload.
     * Mirrors the JS validation in assets/js/pages/suppliers.js.
     */
    public static function supplier(array $data): array
    {
        $errors = [];

        $name    = trim($data['supplier_name'] ?? '');
        $contact = trim($data['contact_person'] ?? '');
        $email   = trim($data['email'] ?? '');
        $phone   = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');

        // Supplier name
        if ($err = self::required($name, 'Supplier name')) $errors[] = $err;
        elseif ($err = self::minLength($name, 2, 'Supplier name')) $errors[] = $err;
        elseif ($err = self::maxLength($name, 100, 'Supplier name')) $errors[] = $err;

        // Contact person (optional but validated if provided)
        if ($contact !== '') {
            if ($err = self::minLength($contact, 2, 'Contact person')) $errors[] = $err;
            if ($err = self::maxLength($contact, 100, 'Contact person')) $errors[] = $err;
        }

        // Email (optional but validated if provided)
        if ($email !== '') {
            if ($err = self::isEmail($email, 'Email')) $errors[] = $err;
        }

        // Phone (optional but validated if provided)
        if ($phone !== '') {
            if ($err = self::isPhone($phone, 'Phone')) $errors[] = $err;
        }

        // Address (optional but length-checked)
        if ($address !== '' && strlen($address) > 255) {
            $errors[] = 'Address must not exceed 255 characters';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate category payload.
     * Mirrors the JS validation in assets/js/pages/categories.js.
     */
    public static function category(array $data): array
    {
        $errors = [];

        $name = trim($data['category_name'] ?? '');

        if ($err = self::required($name, 'Category name')) $errors[] = $err;
        elseif ($err = self::minLength($name, 2, 'Category name')) $errors[] = $err;
        elseif ($err = self::maxLength($name, 50, 'Category name')) $errors[] = $err;

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate transaction payload.
     * Mirrors the JS validation in assets/js/pages/transactions.js.
     */
    public static function transaction(array $data): array
    {
        $errors = [];

        $productId = $data['product_id'] ?? null;
        $typeId    = $data['transaction_type_id'] ?? null;
        $quantity  = $data['quantity'] ?? null;
        $unitPrice = $data['unit_price'] ?? null;
        $date      = trim($data['transaction_date'] ?? '');

        // Product
        if ($err = self::required($productId, 'Product')) {
            $errors[] = $err;
        } elseif ($err = self::isInteger($productId, 'Product')) {
            $errors[] = $err;
        }

        // Transaction type
        if ($err = self::required($typeId, 'Transaction type')) {
            $errors[] = $err;
        } elseif ($err = self::isInteger($typeId, 'Transaction type')) {
            $errors[] = $err;
        }

        // Quantity
        if ($err = self::required($quantity, 'Quantity')) {
            $errors[] = $err;
        } else {
            if ($err = self::isInteger($quantity, 'Quantity')) $errors[] = $err;
            elseif ($err = self::isPositive((int)$quantity, 'Quantity')) $errors[] = $err;
            elseif ($err = self::maxValue((int)$quantity, 99999, 'Quantity')) $errors[] = $err;
        }

        // Unit price
        if ($err = self::required($unitPrice, 'Unit price')) {
            $errors[] = $err;
        } else {
            if ($err = self::isNumeric($unitPrice, 'Unit price')) $errors[] = $err;
            elseif ($err = self::isNonNegative((float)$unitPrice, 'Unit price')) $errors[] = $err;
        }

        // Date
        if ($err = self::required($date, 'Transaction date')) {
            $errors[] = $err;
        } elseif ($err = self::isDate($date, 'Transaction date')) {
            $errors[] = $err;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate user creation / update payload.
     * Mirrors the JS validation in assets/js/pages/users.js.
     */
    public static function user(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        $username  = trim($data['username'] ?? '');
        $email     = trim($data['email'] ?? '');
        $firstName = trim($data['first_name'] ?? '');
        $lastName  = trim($data['last_name'] ?? '');
        $role      = $data['role'] ?? null;
        $password  = $data['password'] ?? '';

        // Username
        if ($err = self::required($username, 'Username')) $errors[] = $err;
        elseif ($err = self::minLength($username, 3, 'Username')) $errors[] = $err;
        elseif ($err = self::maxLength($username, 30, 'Username')) $errors[] = $err;
        elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, and underscores';
        }

        // Email
        if ($err = self::required($email, 'Email')) $errors[] = $err;
        elseif ($err = self::isEmail($email, 'Email')) $errors[] = $err;

        // First name
        if ($err = self::required($firstName, 'First name')) $errors[] = $err;
        elseif ($err = self::maxLength($firstName, 50, 'First name')) $errors[] = $err;

        // Last name
        if ($err = self::required($lastName, 'Last name')) $errors[] = $err;
        elseif ($err = self::maxLength($lastName, 50, 'Last name')) $errors[] = $err;

        // Role
        if ($err = self::inSet($role, ['admin', 'staff'], 'Role')) $errors[] = $err;

        // Password (required on create, optional on update)
        if (!$isUpdate) {
            if ($err = self::required($password, 'Password')) $errors[] = $err;
            elseif ($err = self::minLength($password, 8, 'Password')) $errors[] = $err;
        } elseif ($password !== '') {
            if ($err = self::minLength($password, 8, 'Password')) $errors[] = $err;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate password change payload.
     * Mirrors password validation in assets/js/pages/profile.js.
     */
    public static function passwordChange(array $data): array
    {
        $errors = [];

        $current  = $data['current_password'] ?? '';
        $newPass  = $data['new_password'] ?? '';
        $confirm  = $data['confirm_password'] ?? '';

        if ($err = self::required($current, 'Current password')) $errors[] = $err;

        if ($err = self::required($newPass, 'New password')) {
            $errors[] = $err;
        } elseif ($err = self::minLength($newPass, 8, 'New password')) {
            $errors[] = $err;
        } elseif (!preg_match('/[A-Z]/', $newPass)) {
            $errors[] = 'New password must contain at least one uppercase letter';
        } elseif (!preg_match('/[0-9]/', $newPass)) {
            $errors[] = 'New password must contain at least one number';
        }

        if ($err = self::required($confirm, 'Confirm password')) {
            $errors[] = $err;
        } elseif ($newPass !== $confirm) {
            $errors[] = 'Passwords do not match';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate a CSV row for product import.
     * Mirrors the JS and PHP CSV import validation in db/import_db.php.
     */
    public static function csvProductRow(array $row, int $rowNumber): array
    {
        $errors = [];

        $name     = trim($row['product_name'] ?? '');
        $price    = $row['price'] ?? null;
        $quantity = $row['quantity'] ?? null;
        $prefix   = "Row $rowNumber: ";

        if ($err = self::required($name, 'Product name')) $errors[] = $prefix . $err;
        elseif ($err = self::maxLength($name, 100, 'Product name')) $errors[] = $prefix . $err;

        if ($err = self::required($price, 'Price')) {
            $errors[] = $prefix . $err;
        } elseif ($err = self::isNumeric($price, 'Price')) {
            $errors[] = $prefix . $err;
        } elseif ($err = self::isNonNegative((float)$price, 'Price')) {
            $errors[] = $prefix . $err;
        }

        if ($err = self::required($quantity, 'Quantity')) {
            $errors[] = $prefix . $err;
        } elseif ($err = self::isInteger($quantity, 'Quantity')) {
            $errors[] = $prefix . $err;
        } elseif ($err = self::isNonNegative((int)$quantity, 'Quantity')) {
            $errors[] = $prefix . $err;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate a CSV row for supplier import.
     */
    public static function csvSupplierRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $prefix = "Row $rowNumber: ";

        $name = trim($row['supplier_name'] ?? '');
        if ($err = self::required($name, 'Supplier name')) $errors[] = $prefix . $err;

        $email = trim($row['email'] ?? '');
        if ($email !== '' && ($err = self::isEmail($email, 'Email'))) {
            $errors[] = $prefix . $err;
        }

        $phone = trim($row['phone'] ?? '');
        if ($phone !== '' && ($err = self::isPhone($phone, 'Phone'))) {
            $errors[] = $prefix . $err;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
