<?php
/* @var $content string Mail contents as view render result */
?>

<?php $this->beginPage() ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body style="padding: 0; margin: 0;">
<?= $content ?>

<footer style="margin-top: 5em">
-- <br>
Mailed by Carwow
</footer>
</body>
</html>
<?php $this->endPage() ?>
