<?php //tests for the URL-building fallback used when `.htaccess` / mod_rewrite is unavailable
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

use PHPUnit\Framework\TestCase;

class UrlNoHtaccessTest extends TestCase {
        //`HTACCESS` is a constant, so exercising the fallback means running 'tests/fixtures/urls-no-htaccess.php' in a
        //new process with `NNF_TEST_HTACCESS=0` set
        public function testFallbackUrls () {
                $script = __DIR__.'/fixtures/urls-no-htaccess.php';
                $output = shell_exec (
                        'NNF_TEST_HTACCESS=0 '.escapeshellarg (PHP_BINARY).' '.escapeshellarg ($script).' 2>&1'
                );
                $this->assertSame (
                        "/index.php\n".
                        "/thread.php?file=thread&page=2\n".
                        "/thread.php?path=sub/&file=thread\n".
                        "/index.php?delete\n".
                        "/thread.php?append=abc&file=thread\n",
                        $output
                );
        }
}
