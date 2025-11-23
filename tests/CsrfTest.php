<?php
use PHPUnit\Framework\TestCase;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock session
        if (session_status() === PHP_SESSION_NONE) {
            // session_start() might fail in CLI if headers sent, so we mock $_SESSION directly if needed
            // But for this simple test, we can just rely on the array
            $_SESSION = [];
        }

        // Include the file to test
        // We use require_once to avoid re-declaring functions
        require_once __DIR__ . '/../includes/csrf.php';
    }

    public function testGenerateCsrfToken()
    {
        // Clear session
        $_SESSION = [];

        $token = generate_csrf_token();

        $this->assertNotEmpty($token);
        $this->assertEquals($_SESSION['csrf_token'], $token);
    }

    public function testVerifyCsrfToken()
    {
        $_SESSION = [];
        $token = generate_csrf_token();

        $this->assertTrue(verify_csrf_token($token));
        $this->assertFalse(verify_csrf_token('invalid_token'));
        $this->assertFalse(verify_csrf_token(''));
    }
}
?>