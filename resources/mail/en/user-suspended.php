Hello <?= $username ?>,
<p>You have been suspended on the site <a href="<?= $applicationParameters->getUrl() ?>"><?= $applicationParameters->getName() ?></a>.</p>
<p>If you have any questions about the suspension, please, contact site Administration by <a href="mailto:<?= $applicationParameters->getSupportEmail() ?>"><?= $applicationParameters->getSupportEmail() ?></a></p>
<br><br>
Best,
The <?= $applicationParameters->getName() ?> team
