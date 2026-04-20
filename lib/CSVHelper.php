<?php

/**
 * CSV import and export utilities.
 * Centralises the CSV handling logic scattered across db/import_db.php and the
 * client-side CSV export in assets/js/app.js (IMS.exportCSV).
 */
class CSVHelper
{
    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    /**
     * Parse an uploaded CSV file into an array of associative rows.
     * The first row must contain headers; they become array keys.
     *
     * @param  string   $filePath      Temporary file path from $_FILES.
     * @param  string[] $requiredCols  Column names that must be present in the header.
     * @return array{rows: array[], errors: string[]}
     */
    public static function parseUpload(string $filePath, array $requiredCols = []): array
    {
        $errors = [];
        $rows   = [];

        if (!is_readable($filePath)) {
            return ['rows' => [], 'errors' => ['Cannot read uploaded file']];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['rows' => [], 'errors' => ['Failed to open uploaded file']];
        }

        // Read header row
        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            return ['rows' => [], 'errors' => ['CSV file is empty or has no header row']];
        }

        // Normalise header names
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        // Check required columns
        foreach ($requiredCols as $col) {
            if (!in_array(strtolower($col), $header, true)) {
                $errors[] = "Missing required column: $col";
            }
        }
        if (!empty($errors)) {
            fclose($handle);
            return ['rows' => [], 'errors' => $errors];
        }

        $lineNumber = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;

            // Skip entirely blank rows
            if (count(array_filter($data, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            if (count($data) !== count($header)) {
                $errors[] = "Row $lineNumber: column count mismatch (expected " . count($header) . ", got " . count($data) . ")";
                continue;
            }

            $rows[] = array_combine($header, $data);
        }

        fclose($handle);
        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * Validate and return the MIME type / extension of an uploaded file.
     * Returns an error string on failure, or null on success.
     */
    public static function validateUploadedFile(array $file): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return 'File upload failed (error code: ' . ($file['error'] ?? 'unknown') . ')';
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return 'Only CSV files are accepted';
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($mime, $allowedMimes, true)) {
            return "Invalid file type ($mime). Please upload a CSV file.";
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB
        if (($file['size'] ?? 0) > $maxBytes) {
            return 'File is too large. Maximum size is 5 MB.';
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Export
    // -------------------------------------------------------------------------

    /**
     * Send an array of associative rows as a CSV file download.
     * Mirrors the IMS.exportCSV() helper in assets/js/app.js, but done server-side
     * so large datasets don't need to be loaded into the browser first.
     *
     * @param array    $rows      Array of associative arrays (same structure).
     * @param string[] $columns   Keys to include, in order. Defaults to all keys.
     * @param string[] $headers   Column header labels. Defaults to the column keys.
     * @param string   $filename  Download filename (without .csv extension).
     */
    public static function download(
        array  $rows,
        array  $columns  = [],
        array  $headers  = [],
        string $filename = 'export'
    ): never {
        if (empty($rows)) {
            // Still produce a valid (header-only) CSV
        }

        if (empty($columns) && !empty($rows)) {
            $columns = array_keys(reset($rows));
        }

        if (empty($headers)) {
            $headers = array_map(fn($c) => ucwords(str_replace('_', ' ', $c)), $columns);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::sanitizeFilename($filename) . '.csv"');
        header('Cache-Control: no-store, no-cache');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM so Excel opens it correctly
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }

    /**
     * Build an in-memory CSV string from rows.
     * Useful for attaching to emails or storing in the filesystem.
     *
     * @param array    $rows
     * @param string[] $columns
     * @param string[] $headers
     */
    public static function toString(array $rows, array $columns = [], array $headers = []): string
    {
        if (empty($columns) && !empty($rows)) {
            $columns = array_keys(reset($rows));
        }
        if (empty($headers)) {
            $headers = array_map(fn($c) => ucwords(str_replace('_', ' ', $c)), $columns);
        }

        $buf    = fopen('php://memory', 'r+');
        fputcsv($buf, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($buf, $line);
        }

        rewind($buf);
        $csv = stream_get_contents($buf);
        fclose($buf);
        return $csv;
    }

    // -------------------------------------------------------------------------
    // Template generation (matches /assets/templates/ patterns)
    // -------------------------------------------------------------------------

    /** Return the CSV header template for product bulk import. */
    public static function productImportTemplate(): string
    {
        return self::toString(
            [],
            ['product_name', 'description', 'price', 'quantity', 'low_stock_threshold', 'category_name', 'supplier_name'],
            ['Product Name', 'Description', 'Price', 'Quantity', 'Low Stock Threshold', 'Category Name', 'Supplier Name']
        );
    }

    /** Return the CSV header template for supplier bulk import. */
    public static function supplierImportTemplate(): string
    {
        return self::toString(
            [],
            ['supplier_name', 'contact_person', 'email', 'phone', 'address'],
            ['Supplier Name', 'Contact Person', 'Email', 'Phone', 'Address']
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^\w\-.]/', '_', $name);
    }
}
