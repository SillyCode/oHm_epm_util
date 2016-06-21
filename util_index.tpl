<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<link rel='stylesheet' href='utils.css'>
		<script src='jquery-1.11.1.min.js'></script>
		<script type="text/javascript" src="utils.js"></script>
	</head>
	<body>
		<div class="tabs">
			<ul>
			{loop tabs}<li><a href="{url}">{text}</a></li>{/loop tabs}
			</ul>
		</div>
		{content}
	</body>
</html>
