Bonjour,

<p>Une nouvelle concession a été enregistrée.</p>
<p>
Nom: <?= $dealershipName ?><br>
Numéro d'entreprise: <?= $businessNumber ?><br>
Adresse de la concession: <?= $dealershipAddress ?>, <?= $dealershipProvince ?><br>
Code postal: <?= $dealershipPostalCode ?><br>
Site web: <?= $webSite ?><br>
</p>

<p>
Nom d'utilisateur: <?= $username ?><br>
E-mail: <?= $email ?><br>
Adresse du représentant:<?= $representativeAddress ?>, <?= $representativeProvince ?>
Code postal: <?= $representativePostalCode ?><br>
Téléphone: <?= $phone ?><br>
Numéro de licence: <?= $licenseNumber ?><br>
</p>

<p>
    <a href="<?= $urlGenerator->generateAbsolute('admin.approveDealer', ['_language' => 'fr', 'id' => $dealerId]) ?>">Approve</a>
</p>

Cordialement,
L'équipe <?= $applicationParameters->getName() ?>
