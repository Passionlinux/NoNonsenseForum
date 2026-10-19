<?php http_response_code (500); //all of the error pages are server-side configuration / permission problems ?>
<!DOCTYPE html>
<title>PHP 7.3 Required</title>
<h1>NoNonsense Forum</h2>
<h2>Error: PHP 7.3 Required</h2>
<p>
	A minimum PHP version of 7.3 is required to run NoNonsense Forum
	(secure password hashing needs `password_hash` / `hash_equals`, security headers need
	`random_bytes`, and the language cookie uses the `SameSite` attribute of `setcookie`).
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