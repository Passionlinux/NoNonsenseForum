<?php //test bootstrap
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite bootstrap

   The shared functions in 'lib/functions.php' expect a handful of constants to be defined by 'start.php'
   (paths, URL settings &c.). this bootstrap provides a static set of those so that the functions can be tested
   in isolation, without booting the whole forum.
*/

define ('FORUM_ROOT',  dirname (__DIR__));
define ('FORUM_LIB',   FORUM_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);

//URL / request settings
define ('FORUM_PATH',  '/');
define ('PATH_URL',    '');
//`HTACCESS` is a constant, so the fallback (non-pretty URL) mode can only be tested in a fresh process; see
//'tests/fixtures/urls-no-htaccess.php'
define ('HTACCESS',    getenv ('NNF_TEST_HTACCESS') !== '0');
define ('FORUM_HOST',  'forum.test');
define ('FORUM_URL',   'http://forum.test');
define ('FORUM_HTTPS', false);

//site settings
define ('FORUM_NAME',    'NoNonsense Forum');
//constants that 'start.php' derives from the request / configuration
define ('PATH',          '');
define ('NAME',          '');
define ('AUTH_HTTP',     false);
define ('CSP_NONCE',     'phpunit-nonce');
define ('FORUM_THREADS', 50);
define ('FORUM_POSTS',   25);
define ('THEME_ROOT',    FORUM_ROOT.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'greyscale'.DIRECTORY_SEPARATOR);
define ('THEME_LANGS',   '');
define ('LANG',          '');

//the mod / member lists and translations are globals set up by 'start.php'; tests can overwrite these
$GLOBALS['LANG']    = array ();
$GLOBALS['MODS']    = array ('GLOBAL' => array (), 'LOCAL' => array ());
$GLOBALS['MEMBERS'] = array ();
