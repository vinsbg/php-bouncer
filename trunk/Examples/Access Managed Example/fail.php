<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>Failure</title>
</head>
<body>
<p>It seems we have a problem.</p>

<p>URL = <?php echo urldecode($_GET["url"]); ?></p>
<pre><?php
	$roles = urldecode(unserialize($_GET["roles"]));
	print_r($roles);
	?></pre>
<?php include("nav.php"); ?>
</body>
</html>