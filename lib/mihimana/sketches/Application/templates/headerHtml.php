<?php if (DEBUG): ?>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />      
<?php endif; ?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php //default style sheets ?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php renderAsset('css/bases.css', true) ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php renderAsset('css/mihimana.css', true) ?>" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php renderAsset('css/jquery.ui.css', true) ?>" />
<link rel="stylesheet" href="<?php renderAsset('js/codeMirror/lib/codemirror.css', true) ?>">
<link rel="stylesheet" href="<?php renderAsset('js/codeMirror/lib/custom.css', true) ?>">

<?php //default javascript ?>
<script type="text/javascript" src="<?php renderAsset('js/jquery.js', true) ?>"></script>
<script type="text/javascript" src="<?php renderAsset('js/jquery-ui.js', true) ?>"></script>
<script type="text/javascript" src="<?php renderAsset('js/jquery.tools.min.js', true) ?>"></script>
<script type="text/javascript" src="<?php renderAsset('js/jqModal.js', true) ?>"></script>
<script type="text/javascript" src="<?php renderAsset('js/ckeditor/ckeditor.js', true) ?>"></script>
<script src="<?php renderAsset('js/codeMirror/lib/codemirror.js', true) ?>"></script>
<script src="<?php renderAsset('js/codeMirror/mode/javascript/javascript.js', true) ?>"></script>
<script src="<?php renderAsset('js/mihimana.js', true) ?>"></script>
<link rel="shortcut icon" type="image/png" href="<?php renderAsset('images/favicon.ico', true) ?>" />
