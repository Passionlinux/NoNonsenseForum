<?php //tests for the UTF-8 safe input / output functions in 'lib/utf8safe.php'
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

use PHPUnit\Framework\TestCase;

class Utf8SafeTest extends TestCase {
        //`safeHTML` is used for anything going into an HTML element. it must encode the dangerous characters (`<`, `>`, `&`)
        //but deliberately leaves double-quotes alone, because `formatText` uses literal `"` characters for quote markup
        public function testSafeHTMLEncodesTagsButLeavesQuotes () {
                $this->assertSame ('&lt;b&gt;"x" &amp; y&lt;/b&gt;', safeHTML ('<b>"x" & y</b>'));
        }
        
        //`safeAttr` is the counterpart for attribute values: quotes *must* be encoded or the value could break out
        public function testSafeAttrEncodesQuotes () {
                $this->assertSame ('a&quot;b&#039;c&amp;d', safeAttr ('a"b\'c&d'));
        }
        
        //`safeURL` URL-encodes everything except the path separator
        public function testSafeURLKeepsSlashes () {
                $this->assertSame ('a%20b/c%3Fd%3De', safeURL ('a b/c?d=e'));
        }
        
        //`safeTrim` should strip Unicode whitespace (here a non-breaking space), not just ASCII
        public function testSafeTrimHandlesUnicodeWhitespace () {
                $text = "\u{00A0} x \t\n";
                $this->assertSame ('x', safeTrim ($text), 'safeTrim should strip non-breaking spaces');
        }
        
        //normalise characters before output (the framework's `Trim` only handles ASCII)
        public function testSafeTransliterateFoldsAccents () {
                $this->assertSame ('hello-woerld', safeTransliterate ('Héllo Wörld!'));
        }
        
        public function testSafeTransliterateFoldsSymbols () {
                $this->assertSame ('100degc-5eur', safeTransliterate ('100℃ & 5€'));
        }
        
        //a string with nothing transliterable must not come back blank (it is used to build unique HTML IDs)
        public function testSafeTransliterateNeverBlank () {
                $this->assertSame ('_', safeTransliterate ('!!!'));
        }
}
