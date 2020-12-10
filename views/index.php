<!doctype html>
<html>
	<head>
		<title>Replay Zipping Tool - Tooth and Tail</title>
		<link href="public/css/styles.css" rel="stylesheet">
		<link href="public/css/pushy-buttons.min.css" rel="stylesheet">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<img id="logo" src="public/img/tnt-logo.png" alt="Tooth and Tail logo">
		<h1 id="title">Replay Zipping Tool</h1>
		<div id="description">
			<p>Select the replays of the series you just played and we take care of the rest:</p>
			<ol>
				<li>It renames the files with the players names</li>
				<li>It orders and numbers them correctly</li>
				<li>It fills with dummies to avoid spoilers</li>
				<li>Then finally zips them =)</li>
			</ol>
		</div>
		<form method="post" action="actions/receive_form.php" enctype="multipart/form-data" id="form">
			<div id="file-container">
				<input type="file" name="replays[]" accept="text/xml" multiple="multiple" required="required">
				<p>Drag your files here or click in this area</p>
			</div>
			<p><input type="submit" value="Create" id="button" class="pushy__btn pushy__btn--lg pushy__btn--blue"></p>
		</form>
		<script>
			document.querySelector('#file-container input').addEventListener('change', function() {
				document.querySelector('#file-container p').innerHTML = this.files.length + ' file(s) selected';
			});
		</script>
	</body>
</html>