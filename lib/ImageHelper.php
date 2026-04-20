<?php

/**
 * Image upload and management utilities for product images.
 * Centralises the inline upload logic found in db/product_db.php so it can be
 * reused and tested independently.
 */
class ImageHelper
{
    private static string $uploadDir     = __DIR__ . '/../assets/images/products/';
    private static string $uploadUrlBase = '/assets/images/products/';

    private static array  $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private static int $maxBytes = 5 * 1024 * 1024; // 5 MB

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Validate an uploaded image file.
     * Returns an error string on failure, or null on success.
     */
    public static function validate(array $file): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $codes = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload',
            ];
            return $codes[$file['error'] ?? -1] ?? 'Unknown upload error';
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowedExtensions, true)) {
            return 'Only JPG, PNG, GIF, and WebP images are allowed';
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::$allowedMimes, true)) {
            return "Invalid image type ($mime)";
        }

        if (($file['size'] ?? 0) > self::$maxBytes) {
            return 'Image is too large. Maximum size is 5 MB.';
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Storage
    // -------------------------------------------------------------------------

    /**
     * Move an uploaded image to the products upload directory.
     * Returns the public URL path on success, or throws on failure.
     *
     * @param  array  $file  Entry from $_FILES.
     * @return string        Public URL, e.g. "/assets/images/products/abc123.jpg"
     * @throws \RuntimeException
     */
    public static function store(array $file): string
    {
        self::ensureUploadDir();

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = self::generateFilename($ext);
        $destPath = self::$uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Failed to move uploaded file to storage');
        }

        return self::$uploadUrlBase . $filename;
    }

    /**
     * Delete a stored product image given its public URL path.
     * Silently ignores missing files.
     *
     * @param  string $urlPath  e.g. "/assets/images/products/abc123.jpg"
     */
    public static function delete(string $urlPath): void
    {
        if (!$urlPath) return;

        $filename = basename($urlPath);
        $fullPath = self::$uploadDir . $filename;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Replace an existing product image:
     *   1. Delete the old image (if any).
     *   2. Store the new image.
     *
     * @param  array  $file        New uploaded file from $_FILES.
     * @param  string $oldUrlPath  Existing image URL to delete (may be empty).
     * @return string              Public URL of the new image.
     * @throws \RuntimeException
     */
    public static function replace(array $file, string $oldUrlPath = ''): string
    {
        if ($oldUrlPath) {
            self::delete($oldUrlPath);
        }
        return self::store($file);
    }

    // -------------------------------------------------------------------------
    // Introspection
    // -------------------------------------------------------------------------

    /**
     * Return basic metadata about a stored image.
     *
     * @param  string $urlPath  Public URL path.
     * @return array{exists: bool, width: int, height: int, size: int, mime: string}
     */
    public static function info(string $urlPath): array
    {
        $empty = ['exists' => false, 'width' => 0, 'height' => 0, 'size' => 0, 'mime' => ''];
        if (!$urlPath) return $empty;

        $fullPath = self::$uploadDir . basename($urlPath);
        if (!is_file($fullPath)) return $empty;

        $size = filesize($fullPath);
        $mime = mime_content_type($fullPath);
        [$width, $height] = @getimagesize($fullPath) ?: [0, 0];

        return [
            'exists' => true,
            'width'  => (int)$width,
            'height' => (int)$height,
            'size'   => (int)$size,
            'mime'   => (string)$mime,
        ];
    }

    /**
     * Return true if a given URL path points to an existing image on disk.
     */
    public static function exists(string $urlPath): bool
    {
        if (!$urlPath) return false;
        return is_file(self::$uploadDir . basename($urlPath));
    }

    /**
     * Return a list of all stored product images with metadata.
     *
     * @return array[]
     */
    public static function listAll(): array
    {
        self::ensureUploadDir();
        $results = [];
        $pattern = self::$uploadDir . '*.{jpg,jpeg,png,gif,webp}';
        foreach (glob($pattern, GLOB_BRACE) as $path) {
            $filename    = basename($path);
            [$w, $h]     = @getimagesize($path) ?: [0, 0];
            $results[]   = [
                'filename' => $filename,
                'url'      => self::$uploadUrlBase . $filename,
                'size'     => filesize($path),
                'width'    => (int)$w,
                'height'   => (int)$h,
                'modified' => date('Y-m-d H:i:s', filemtime($path)),
            ];
        }
        return $results;
    }

    /**
     * Delete all product images that are no longer referenced in the database.
     * Returns the number of files removed.
     *
     * @param  string[] $referencedUrls  Array of URL paths currently stored in the DB.
     */
    public static function purgeOrphans(array $referencedUrls): int
    {
        self::ensureUploadDir();
        $referenced = array_map('basename', $referencedUrls);
        $count      = 0;
        $pattern    = self::$uploadDir . '*.{jpg,jpeg,png,gif,webp}';
        foreach (glob($pattern, GLOB_BRACE) as $path) {
            if (!in_array(basename($path), $referenced, true)) {
                @unlink($path);
                $count++;
            }
        }
        return $count;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function generateFilename(string $ext): string
    {
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    private static function ensureUploadDir(): void
    {
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0755, true);
        }
    }
}
