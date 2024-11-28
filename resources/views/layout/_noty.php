<?php

use App\Backend\Service\NotyService;
use Yiisoft\Json\Json;

/**
 * @var NotyService $noty
 * @var \Yiisoft\Session\SessionInterface $session
*/
$noty = new NotyService($session);
$messages = $noty->getAllAndFlush();
?>

<script>
    let noty;
    let closeWith;
</script>

<?php foreach ($messages as $message) { ?>
    <script>
        noty = new Noty({
            type: '<?= $message->type ?>',
            layout: '<?= $message->layout ?>',
            text: decodeURIComponent('<?= rawurlencode($message->text) ?>'),
            timeout: <?= $message->timeout ?>,
        });

        <?php if (!empty($message->closeWith)) { ?>
            closeWith = JSON.parse('<?= Json::encode($message->closeWith) ?>');
            noty.options.closeWith = closeWith;
        <?php } ?>

        noty.show();
    </script>
<?php } ?>
