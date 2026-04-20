<?php

/**
 * Pagination and filtering utilities for API endpoints.
 * Provides server-side equivalents of the client-side DataTable pagination
 * configured via IMS.initDataTable() in assets/js/app.js.
 */
class PaginationHelper
{
    public const DEFAULT_PER_PAGE = 25;
    public const MAX_PER_PAGE     = 200;

    // -------------------------------------------------------------------------
    // Parameter extraction
    // -------------------------------------------------------------------------

    /**
     * Extract and sanitise pagination parameters from $_GET.
     *
     * @return array{page: int, per_page: int, offset: int}
     */
    public static function fromRequest(): array
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(
            self::MAX_PER_PAGE,
            max(1, (int)($_GET['per_page'] ?? self::DEFAULT_PER_PAGE))
        );
        return [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => ($page - 1) * $perPage,
        ];
    }

    /**
     * Extract a search/filter term from $_GET, sanitised for safe use in LIKE
     * queries via a prepared statement placeholder (returns the raw term; the
     * caller should bind it with "%$term%").
     */
    public static function searchTerm(string $key = 'search'): string
    {
        return trim($_GET[$key] ?? '');
    }

    /**
     * Extract a sort column and direction from $_GET.
     *
     * @param  string[] $allowedColumns  Whitelist of column names that may be sorted.
     * @param  string   $defaultColumn   Column to fall back to.
     * @param  string   $defaultDir      'ASC' or 'DESC'.
     * @return array{column: string, direction: string}
     */
    public static function sortParams(
        array  $allowedColumns,
        string $defaultColumn = 'created_at',
        string $defaultDir    = 'DESC'
    ): array {
        $col = $_GET['sort_by'] ?? $defaultColumn;
        if (!in_array($col, $allowedColumns, true)) {
            $col = $defaultColumn;
        }

        $dir = strtoupper($_GET['sort_dir'] ?? $defaultDir);
        if (!in_array($dir, ['ASC', 'DESC'], true)) {
            $dir = $defaultDir;
        }

        return ['column' => $col, 'direction' => $dir];
    }

    // -------------------------------------------------------------------------
    // In-memory pagination (for small datasets already in PHP)
    // -------------------------------------------------------------------------

    /**
     * Slice an array of rows to a single page.
     *
     * @param  array $rows     Full result set.
     * @param  int   $page     1-based page number.
     * @param  int   $perPage  Items per page.
     * @return array           Sliced rows.
     */
    public static function slice(array $rows, int $page, int $perPage): array
    {
        return array_slice($rows, ($page - 1) * $perPage, $perPage);
    }

    /**
     * Build a pagination metadata array from totals and current position.
     *
     * @return array{total: int, per_page: int, current_page: int, last_page: int, from: int, to: int}
     */
    public static function meta(int $total, int $page, int $perPage): array
    {
        $lastPage = (int)ceil($total / max(1, $perPage));
        return [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage,
            'from'         => $total === 0 ? 0 : ($page - 1) * $perPage + 1,
            'to'           => min($page * $perPage, $total),
        ];
    }

    // -------------------------------------------------------------------------
    // SQL query building helpers
    // -------------------------------------------------------------------------

    /**
     * Return a LIMIT/OFFSET clause string for direct inclusion in a SQL query.
     * Values come from pre-validated integer parameters only — safe to interpolate.
     */
    public static function limitClause(int $limit, int $offset): string
    {
        return "LIMIT $limit OFFSET $offset";
    }

    /**
     * Build a SQL WHERE fragment and a matching bind-params list for a full-text
     * search across multiple columns using LIKE.
     *
     * Example:
     *   [$where, $types, $params] = PaginationHelper::likeWhere(['product_name', 'description'], $term);
     *   // $where  → "WHERE (product_name LIKE ? OR description LIKE ?)"
     *   // $types  → "ss"
     *   // $params → ["%foo%", "%foo%"]
     *
     * @param  string[] $columns   Whitelisted column names.
     * @param  string   $term      Raw search term (not yet wrapped in %).
     * @return array{0: string, 1: string, 2: array}
     */
    public static function likeWhere(array $columns, string $term): array
    {
        if ($term === '' || empty($columns)) {
            return ['', '', []];
        }

        $like   = '%' . $term . '%';
        $parts  = array_map(fn($col) => "$col LIKE ?", $columns);
        $where  = 'WHERE (' . implode(' OR ', $parts) . ')';
        $types  = str_repeat('s', count($columns));
        $params = array_fill(0, count($columns), $like);

        return [$where, $types, $params];
    }

    /**
     * Merge two sets of bind params (types string + params array).
     * Useful when combining a WHERE search with LIMIT/OFFSET bindings.
     *
     * @param  string $types1
     * @param  array  $params1
     * @param  string $types2
     * @param  array  $params2
     * @return array{0: string, 1: array}
     */
    public static function mergeBindParams(
        string $types1,
        array  $params1,
        string $types2,
        array  $params2
    ): array {
        return [$types1 . $types2, array_merge($params1, $params2)];
    }
}
