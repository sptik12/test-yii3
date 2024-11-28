Dear <?= $username ?>,
<p>We are excited to inform you that your account has been successfully activated on our platform, and you can now log in to begin publishing your vehicles.</p>
<p>Please follow the link below to access your account: <a href="<?= $applicationParameters->getUrl() ?>"><?= $applicationParameters->getName() ?></a></p>
<p>If you have any questions or need assistance, our support team is here to help. Feel free to reach out at
<a href="mailto:<?= $applicationParameters->getSupportEmail() ?>"><?= $applicationParameters->getSupportEmail() ?></a> or <?= $applicationParameters->getPhone() ?>.</p>

Best regards,
The <?= $applicationParameters->getName() ?> team
