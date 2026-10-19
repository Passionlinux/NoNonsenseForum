<?php //tests for the message-markup parser (`formatText` / `unformatText` in 'lib/functions.php')
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

use PHPUnit\Framework\TestCase;

class FormatTextTest extends TestCase {
        //a plain line becomes a single paragraph (note the long-standing leading newline)
        public function testPlainTextBecomesAParagraph () {
                $this->assertSame ("\n<p>\nHello world\n</p>", formatText ('Hello world'));
        }
        
        //the first thing `formatText` must do is neutralise HTML injection
        public function testHTMLIsEscaped () {
                $this->assertSame (
                        "\n<p>\n&lt;script&gt;alert(1)&lt;/script&gt;\n</p>",
                        formatText ('<script>alert(1)</script>')
                );
        }
        
        public function testQuotesBecomeBlockquotes () {
                $this->assertSame (
                        "\n<blockquote>\n<p>\n<span class=\"ql\">&ldquo;</span>quoted text<span class=\"qr\">&rdquo;</span>\n</p>\n</blockquote>",
                        formatText ('"quoted text"')
                );
        }
        
        public function testDivider () {
                $this->assertSame (
                        "\n<p>\nabove\n</p>\n<p class=\"hr\">---</p>\n<p>\nbelow\n</p>",
                        formatText ("above\n\n---\n\nbelow")
                );
        }
        
        public function testEmphasis () {
                $this->assertSame ("\n<p>\n<em>_em_</em> and <strong>*bold*</strong>\n</p>", formatText ('_em_ and *bold*'));
        }
        
        public function testInlineCode () {
                $this->assertSame ("\n<p>\nuse <code>`ls -l`</code> here\n</p>", formatText ('use `ls -l` here'));
        }
        
        public function testIndentedCodeBlock () {
                $this->assertSame (
                        "\n<pre><span class=\"ct\">% title</span>\n\$ ls\n<span class=\"cb\">%</span></pre>",
                        formatText ("% title\n\$ ls\n%")
                );
        }
        
        //two titles with the same text must get distinct HTML IDs so that anchors don't collide
        public function testTitlesGetUniqueIds () {
                $this->assertSame (
                        "\n<h2 id=\"::same\"><a href=\"#::same\">:: Same</a></h2>".
                        "\n<h2 id=\"::same_1\"><a href=\"#::same_1\">:: Same</a></h2>",
                        formatText (":: Same\n\n:: Same")
                );
        }
        
        //external links get `rel="nofollow external"` ...
        public function testExternalLink () {
                $this->assertSame (
                        "\n<p>\nSee <a href=\"http://example.com/x\" rel=\"nofollow external\">http://example.com/x</a>\n</p>",
                        formatText ('See http://example.com/x')
                );
        }
        
        //... but links back to the forum itself do not
        public function testInternalLinkHasNoRel () {
                $this->assertSame (
                        "\n<p>\n<a href=\"http://forum.test/x\">http://forum.test/x</a>\n</p>",
                        formatText ('http://forum.test/x')
                );
        }
        
        public function testEmailIsLinked () {
                $this->assertSame (
                        "\n<p>\nmail <a href=\"mailto:me@example.com\" rel=\"nofollow external\">me@example.com</a>\n</p>",
                        formatText ('mail me@example.com')
                );
        }
        
        //`@name` links back to that user's last post, but only when the thread (an RSS object) is supplied
        public function testNameReferenceLinksToLastPost () {
                $rss = simplexml_load_string (
                        '<?xml version="1.0" encoding="UTF-8"?>
                         <rss version="2.0"><channel>
                           <item><author>Alice</author><link>/thread.php?file=x#p1</link></item>
                           <item><author>Bob</author><link>/thread.php?file=x#p2</link></item>
                         </channel></rss>'
                );
                $this->assertSame (
                        "\n<p>\n<a href=\"/thread.php?file=x#p2\">@Bob</a> hello there\n</p>",
                        formatText ('@Bob hello there', '', 'p3', $rss)
                );
                //an unknown name is left alone
                $this->assertSame ("\n<p>\n@Nobody hi\n</p>", formatText ('@Nobody hi', '', 'p3', $rss));
        }
        
        //`unformatText` reverses the HTML encoding so that appended text can be re-parsed for unique heading IDs
        public function testUnformatText () {
                $this->assertSame ('<b>hi</b> & bye', unformatText ('&lt;b&gt;hi&lt;/b&gt; &amp; bye'));
        }
}
