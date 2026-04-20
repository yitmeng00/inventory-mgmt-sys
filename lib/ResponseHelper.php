<?php

/**
 * Standardised JSON response helper.
 * Centralises the response envelope pattern used across all db/*.php API files.
 */
class ResponseHelper
{
    /**
     * Send a successful JSON response and exit.
     *
     * @param array  $data        Key-value pairs merged into the envelope.
     * @param int    $statusCode  HTTP status code (default 200).
     */
    public static function success(array $data = [], int $statusCode = 200): never
    {
        http_response_code($statusCode);
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    /**
     * Send an error JSON response and exit.
     *
     * @param string $message     Human-readable error message.
     * @param int    $statusCode  HTTP status code (default 400).
     * @param array  $extra       Additional fields to merge into the envelope.
     */
    public static function error(string $message, int $statusCode = 400, array $extra = []): never
    {
        http_response_code($statusCode);
        echo json_encode(array_merge(['success' => false, 'error_msg' => $message], $extra));
        exit;
    }

    /**
     * Send a validation error response (422 Unprocessable Entity) and exit.
     *
     * @param string[] $errors  List of validation error strings.
     */
    public static function validationError(array $errors): never
    {
        http_response_code(422);
        echo json_encode([
            'success'   => false,
            'error_msg' => $errors[0] ?? 'Validation failed',
            'errors'    => $errors,
        ]);
        exit;
    }

    /**
     * Send a 401 Unauthorized response and exit.
     */
    public static function unauthorized(string $message = 'Authentication required'): never
    {
        self::error($message, 401);
    }

    /**
     * Send a 403 Forbidden response and exit.
     */
    public static function forbidden(string $message = 'Access denied'): never
    {
        self::error($message, 403);
    }

    /**
     * Send a 404 Not Found response and exit.
     */
    public static function notFound(string $resource = 'Resource'): never
    {
        self::error("$resource not found", 404);
    }

    /**
     * Send a 405 Method Not Allowed response and exit.
     */
    public static function methodNotAllowed(): never
    {
        self::error('Method not allowed', 405);
    }

    /**
     * Send a 409 Conflict response and exit.
     */
    public static function conflict(string $message): never
    {
        self::error($message, 409);
    }

    /**
     * Send a 500 Internal Server Error response and exit.
     */
    public static function serverError(string $message = 'Internal server error'): never
    {
        self::error($message, 500);
    }

    /**
     * Assert the HTTP request method matches one of the allowed methods.
     * Sends 405 and exits if it does not.
     *
     * @param string[] $allowed  e.g. ['GET', 'POST']
     */
    public static function requireMethod(array $allowed): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed, true)) {
            self::methodNotAllowed();
        }
    }

    /**
     * Return decoded JSON from php://input, or send 400 and exit if body is invalid.
     */
    public static function jsonBody(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            self::error('Invalid JSON body', 400);
        }
        return $data;
    }

    /**
     * Return a paginated success envelope.
     *
     * @param array $items      The current page of items.
     * @param int   $total      Total number of items across all pages.
     * @param int   $page       Current page number (1-based).
     * @param int   $perPage    Items per page.
     * @param string $itemsKey  Key name for the items array in the envelope.
     */
    public static function paginated(
        array  $items,
        int    $total,
        int    $page,
        int    $perPage,
        string $itemsKey = 'items'
    ): never {
        self::success([
            $itemsKey     => $items,
            'pagination'  => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int)ceil($total / max(1, $perPage)),
                'from'         => ($page - 1) * $perPage + 1,
                'to'           => min($page * $perPage, $total),
            ],
        ]);
    }
}
