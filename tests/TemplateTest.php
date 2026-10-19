<?php //tests for the shared templating in 'prepareTemplate'
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
    licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase {
        protected function setUp (): void {
                parent::setUp ();
                //`prepareTemplate` calls into the theme for the mods / members lists
                require_once THEME_ROOT.'theme.php';
                //keep the shared state predictable: no mods / members in these templates
                $GLOBALS['MODS']    = array ('GLOBAL' => array (), 'LOCAL' => array ());
                $GLOBALS['MEMBERS'] = array ();
        }
        
        /* CSRF token
           -------------------------------------------------------------------------------------------------------------- */
        //every POST form is a state change and must echo the token back; the search box is a GET and must not carry it
        public function testPostFormsCarryCsrfToken () {
                $template = prepareTemplate (THEME_ROOT.'index.html', '', 'test');
                $selector = '//form[translate(@method,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="post"]';
                
                $forms  = count ($template->query ($selector));
                $tokens = count ($template->query ($selector.'/input[@name="csrf" and @value="'.CSRF_TOKEN.'"]'));
                
                $this->assertGreaterThan (0, $forms, 'the template should contain POST forms');
                $this->assertSame ($forms, $tokens, 'every POST form should carry the CSRF token');
        }
        
        public function testGetFormsDoNotCarryCsrfToken () {
                $template = prepareTemplate (THEME_ROOT.'index.html', '', 'test');
                $this->assertCount (0, $template->query ('//form[@method="get"]/input[@name="csrf"]'));
        }
        
        /* conditional requests
           -------------------------------------------------------------------------------------------------------------- */
        //the nonce differs on every response but must not make the page look changed, or a 304 would never be possible
        public function testPageEtagBlanksOutTheNonce () {
                $this->assertSame (
                        pageETag ('<script nonce="'.CSP_NONCE.'">x</script>'),
                        pageETag ('<script nonce="">x</script>')
                );
        }
        
        public function testPageEtagChangesWithTheContent () {
                $this->assertNotSame (pageETag ('<p>a</p>'), pageETag ('<p>b</p>'));
        }
}
