<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private static string $algorithm  = 'HS256';
    private static int    $expiresIn  = 86400; // 24 hours
    private static string $cookieName = 'ims_token';

    private static function secret(): string
    {
        static $loaded = false;
        if (!$loaded) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();
            $loaded = true;
        }
        return $_ENV['JWT_SECRET'] ?? 'change_this_secret_in_env';
    }

    public static function generate(array $payload): string
    {
        $now  = time();
        $data = array_merge($payload, ['iat' => $now, 'exp' => $now + self::$expiresIn]);
        return JWT::encode($data, self::secret(), self::$algorithm);
    }

    public static function decode(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(self::secret(), self::$algorithm));
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function setTokenCookie(string $token): void
    {
        setcookie(self::$cookieName, $token, [
            'expires'  => time() + self::$expiresIn,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public static function clearTokenCookie(): void
    {
        setcookie(self::$cookieName, '', time() - 3600, '/');
    }

    /** Get raw token from cookie. */
    private static function getToken(): ?string
    {
        return $_COOKIE[self::$cookieName] ?? null;
    }

    /**
     * Protect a PHP page. Redirects to login if unauthenticated.
     * Returns the decoded JWT payload on success.
     */
    public static function authenticate(): object
    {
        $token = self::getToken();
        if (!$token || !($payload = self::decode($token))) {
            self::clearTokenCookie();
            header('Location: /login.php');
            exit;
        }
        return $payload;
    }

    /**
     * Protect a PHP page and require admin role.
     */
    public static function requireAdmin(): object
    {
        $payload = self::authenticate();
        if (($payload->role ?? 'staff') !== 'admin') {
            header('Location: /dashboard.php');
            exit;
        }
        return $payload;
    }

    /**
     * Protect an API endpoint. Sends JSON 401 if unauthenticated.
     * Returns the decoded JWT payload on success.
     */
    public static function authenticateAPI(): object
    {
        $token = self::getToken();
        if (!$token || !($payload = self::decode($token))) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        return $payload;
    }

    /**
     * Protect an API endpoint and require admin role.
     */
    public static function requireAdminAPI(): object
    {
        $payload = self::authenticateAPI();
        if (($payload->role ?? 'staff') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin access required']);
            exit;
        }
        return $payload;
    }
}
