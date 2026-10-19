<?php //prints the URLs generated when `.htaccess` / mod_rewrite is *not* available, for the test-suite to compare against
/* ====================================================================================================================== */
/* NoNonsense Forum test-suite © Copyright (CC-BY) Kroc Camen 2010-2015, Passionlinux 2026
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
*/

//`HTACCESS` is a constant (defined by the bootstrap), so the only way to exercise the fallback mode is in a fresh
// process where the bootstrap can be told to define it as false
putenv ('NNF_TEST_HTACCESS=0');

require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../bootstrap.php';

echo url (),                                       "\n";
echo url ('', 'thread', 2),                        "\n";
echo url ('sub/', 'thread'),                       "\n";
echo url ('', '', 0, 'delete'),                    "\n";
echo url ('', 'thread', 0, 'append', 'abc'),       "\n";
