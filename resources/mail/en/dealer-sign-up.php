Hello,

<p>A new dealer has registered on the platform and needs your approval to activate their account. Please review their registration details below and approve to enable access.</p>
<p>
Name: <?= $dealershipName ?><br>
Business Number: <?= $businessNumber ?><br>
Dealership Address: <?= $dealershipAddress ?>, <?= $dealershipProvince ?><br>
Postal Code: <?= $dealershipPostalCode ?><br>
Website: <?= $webSite ?><br>
</p>

<p>
User Name: <?= $username ?><br>
Email: <?= $email ?><br>
Representative Address:<?= $representativeAddress ?>, <?= $representativeProvince ?>
Postal Code: <?= $representativePostalCode ?><br>
Phone: <?= $phone ?><br>
License Number: <?= $licenseNumber ?><br>
</p>

<p>
    <a href="<?= $urlGenerator->generateAbsolute('admin.approveDealer', ['_language' => 'en', 'id' => $dealerId]) ?>">Approve</a>
</p>

Best regards,
The <?= $applicationParameters->getName() ?> team
