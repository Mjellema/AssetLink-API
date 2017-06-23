<!DOCTYPE html>
<html lang="nl">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		
		<meta name="robots" content="index,follow" />
		
		<title>{title}</title>
		<base href="{base}" />
		<link rel="icon" href="public/assetLink/img/favicon.png?744b7d344c7052e7" type="image/x-icon" />
		<link rel="shortcut icon" href="public/assetLink/img/favicon.png?744b7d344c7052e7" type="image/x-icon" />
		<link rel="apple-touch-icon" href="public/assetLink/img/2x/app-icon.png" />
		
		<!-- BEGIN: link -->
			<link href="{link.href}" rel="{link.rel}" />
		<!-- END: link -->
		<!-- BEGIN: style -->
			<link rel="{style.rel}" type="text/css" href="{style.href}" media="{style.media}" />
		<!-- END: style -->
		
		<!-- BEGIN: script -->
			<script type="text/javascript" src="{script}"></script>
		<!-- END: script -->
		
	</head>
	<body>
		{AREA: header}
		{AREA: body}
		{AREA: footer}
	</body>
</html>