<?php

use App\Frontend\Helper\Ancillary;

/** @var array $responseErrors */

?>

<?php if (!empty($responseErrors)) { ?>
    <script>
        FormErrors.showAll(<?= Ancillary::forJs($responseErrors) ?>)
    </script>
<?php } ?>