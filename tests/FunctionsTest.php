<?php //tests for the shared functions in 'lib/functions.php'
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase {
        protected function setUp (): void {
                parent::setUp ();
                //the request environment is global state; put it back how the bootstrap left it
                $GLOBALS['MODS']    = array ('GLOBAL' => array ('Alice'), 'LOCAL' => array ('Bob'));
                $GLOBALS['MEMBERS'] = array ('Carol');
        }
        
        /* malformed request parameters (`?page[]=1`)
           -------------------------------------------------------------------------------------------------------------- */
        //a request can hand an *array* where a string is expected (`?page[]=1`); anything non-string is flattened to ''
        public function testFlattenParamsReducesArraysToBlank () {
                $params = array ('a' => 'x', 'b' => array ('y'), 'c' => NULL, 'd' => 1);
                flattenParams ($params);
                $this->assertSame (array ('a' => 'x', 'b' => '', 'c' => '', 'd' => ''), $params);
        }
        
        /* CSRF (same-origin) checking
           -------------------------------------------------------------------------------------------------------------- */
        public function testSameOriginWhenNoHeadersSent () {
                unset ($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER']);
                $this->assertTrue (isSameOrigin ());
        }
        
        public function testSameOriginForMatchingHost () {
                $_SERVER['HTTP_HOST']   = 'forum.test';
                $_SERVER['HTTP_ORIGIN'] = 'http://forum.test';
                $this->assertTrue (isSameOrigin ());
        }
        
        public function testSameOriginForPinnedForumHost () {
                //`FORUM_HOST` (the canonical host, 'forum.test' in tests) may differ from the host actually addressed
                $_SERVER['HTTP_HOST']   = 'other.test';
                $_SERVER['HTTP_ORIGIN'] = 'http://forum.test';
                $this->assertTrue (isSameOrigin ());
        }
        
        public function testCrossOriginIsRejected () {
                $_SERVER['HTTP_HOST']   = 'forum.test';
                $_SERVER['HTTP_ORIGIN'] = 'http://evil.com';
                $this->assertFalse (isSameOrigin ());
        }
        
        public function testMalformedOriginIsRejected () {
                $_SERVER['HTTP_HOST']   = 'forum.test';
                $_SERVER['HTTP_ORIGIN'] = 'not a url';
                $this->assertFalse (isSameOrigin ());
        }
        
        public function testRefererUsedWhenOriginAbsent () {
                $_SERVER['HTTP_HOST']   = 'forum.test';
                unset ($_SERVER['HTTP_ORIGIN']);
                $_SERVER['HTTP_REFERER'] = 'http://forum.test/thread.php?file=x';
                $this->assertTrue (isSameOrigin ());
                $_SERVER['HTTP_REFERER'] = 'http://evil.com/';
                $this->assertFalse (isSameOrigin ());
        }
        
        /* URL building
           -------------------------------------------------------------------------------------------------------------- */
        public function testUrlBuildsPrettyUrls () {
                $this->assertSame ('/',              url ());
                $this->assertSame ('/thread',        url ('', 'thread'));
                $this->assertSame ('/thread+2',      url ('', 'thread', 2));
                $this->assertSame ('/thread+2?append=abc', url ('', 'thread', 2, 'append', 'abc'));
                $this->assertSame ('/sub/thread',    url ('sub/', 'thread'));
                $this->assertSame ('/?delete',       url ('', '', 0, 'delete'));
        }
        
        /* post IDs
           -------------------------------------------------------------------------------------------------------------- */
        public function testPostIdIsBase36 () {
                $id = postID ();
                $this->assertMatchesRegularExpression ('/^[0-9a-z]+$/', $id);
        }
        
        public function testPostIdIsUnique () {
                $this->assertNotSame (postID (), postID ());
        }
        
        /* moderators / members
           -------------------------------------------------------------------------------------------------------------- */
        public function testAdminIsFirstGlobalMod () {
                $this->assertTrue  (isAdmin ('alice'));
                $this->assertFalse (isAdmin ('bob'));
        }
        
        //a sub-forum mod must be recognised even when the global mod list is non-empty:
        //this is the regression test for the `+` / `array_merge` bug that silently dropped local mods
        public function testLocalModIsRecognisedAlongsideGlobalMods () {
                $this->assertTrue  (isMod ('ALICE'), 'a global mod should be recognised, case-insensitively');
                $this->assertTrue  (isMod ('bob'),   'a local mod must not be lost when merging with the global list');
                $this->assertFalse (isMod ('carol'));
        }
        
        public function testMemberListIsCaseInsensitive () {
                $this->assertTrue  (isMember ('CAROL'));
                $this->assertFalse (isMember ('alice'));
        }
        
        /* password verification
           -------------------------------------------------------------------------------------------------------------- */
        public function testAuthUserAcceptsModernHash () {
                $file = tempnam (sys_get_temp_dir (), 'nnf');
                file_put_contents ($file, password_hash ('secret', PASSWORD_DEFAULT));
                $this->assertTrue  (authUser ($file, 'alice', 'secret'));
                $this->assertFalse (authUser ($file, 'alice', 'wrong'));
                unlink ($file);
        }
        
        //legacy installs stored a plain sha512 hash; on a successful login it must be upgraded in place
        public function testAuthUserMigratesLegacyHash () {
                $file = tempnam (sys_get_temp_dir (), 'nnf');
                file_put_contents ($file, hash ('sha512', 'alice'.'oldpass'));
                $this->assertTrue (authUser ($file, 'alice', 'oldpass'));
                $this->assertStringStartsWith ('$', file_get_contents ($file), 'legacy hash should be re-hashed');
                $this->assertTrue (authUser ($file, 'alice', 'oldpass'), 'the new hash should still verify');
                unlink ($file);
        }
        
        public function testAuthUserRejectsMissingUser () {
                $this->assertFalse (authUser (sys_get_temp_dir ().'/nnf-does-not-exist', 'alice', 'x'));
        }
        
        /* anti-flood
           -------------------------------------------------------------------------------------------------------------- */
        public function testFloodLimitRecordsAndCountsDown () {
                $file = tempnam (sys_get_temp_dir (), 'nnf');
                unlink ($file);
                $_SERVER['REMOTE_ADDR'] = '192.0.2.1';
                
                $this->assertSame (0, floodLimit (60, $file), 'the first post should be allowed');
                $this->assertGreaterThan  (0, floodLimit (60, $file), 'a second post should be refused');
                $this->assertLessThanOrEqual (60, floodLimit (60, $file));
                
                //a different client is not affected by the first one's post
                $_SERVER['REMOTE_ADDR'] = '192.0.2.2';
                $this->assertSame (0, floodLimit (60, $file));
                
                //a window of 0 disables the limit entirely
                $this->assertSame (0, floodLimit (0, $file));
                
                //the client's IP address must never be written to the file in the clear
                $this->assertStringNotContainsString ('192.0.2.1', (string) @file_get_contents ($file));
                @unlink ($file);
        }
        
        public function testFloodLimitPrunesStaleRecords () {
                $file = tempnam (sys_get_temp_dir (), 'nnf');
                file_put_contents ($file, "stale\t".(time ()-7200)."\nfresh\t".time ()."\n");
                $_SERVER['REMOTE_ADDR'] = '192.0.2.3';
                
                floodLimit (60, $file);
                $content = (string) file_get_contents ($file);
                $this->assertStringNotContainsString ("stale\t", $content, 'records older than an hour should be dropped');
                $this->assertStringContainsString    ("fresh\t", $content, 'recent records must be kept');
                @unlink ($file);
        }
        
        /* search excerpts
           -------------------------------------------------------------------------------------------------------------- */
        public function testSearchSnippetMarksTheMatch () {
                $html = searchSnippet ('<p>The quick brown fox jumps over the lazy dog</p>', 'fox', 10);
                $this->assertStringContainsString ('<mark>fox</mark>', $html);
                $this->assertStringStartsWith ('…', $html);
                $this->assertStringEndsWith   ('…', $html);
        }
        
        public function testSearchSnippetIsCaseInsensitiveAndShowsTextOnly () {
                $html = searchSnippet ('<p>Hello <b>World</b> &amp; all</p>', 'world');
                $this->assertStringContainsString    ('<mark>World</mark>', $html);
                $this->assertStringNotContainsString ('<p>', $html);
                $this->assertStringNotContainsString ('<b>', $html);
                $this->assertStringContainsString    ('&amp;', $html);
        }
        
        public function testSearchSnippetEscapesMarkup () {
                $html = searchSnippet ('<p>watch out for &lt;script&gt; tags</p>', 'script');
                $this->assertStringNotContainsString ('<script>', $html);
                $this->assertStringContainsString    ('<mark>script</mark>', $html);
                $this->assertStringContainsString    ('&lt;', $html);
        }
        
        public function testSearchSnippetWithoutAMatchShowsTheBeginning () {
                $html = searchSnippet ('<p>Nothing to see here</p>', 'zebra');
                $this->assertStringStartsWith ('Nothing to see here', $html);
        }
}
