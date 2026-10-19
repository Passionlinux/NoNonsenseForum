<!DOCTYPE html>
<title>PHP 5.6 Required</title>
<h1>NoNonsense Forum</h2>
<h2>Error: PHP 5.6 Required</h2>
<p>
	A minimum PHP version of 5.6 is required to run NoNonsense Forum
	(secure password hashing needs `password_hash` and `hash_equals`).
	Your server reports it has version <?php echo PHP_VERSION ?>.
</p>
<dl>

<dt><strong>Check your hosting control panel for a version switch:</strong></dt>
<dd><p>
	With some hosting companies you can switch between PHP versions.<br />
	Check your hosting control panel, or contact your hosting company for support.
</p></dd>

</dl>
<?php exit (1); ?>