<div class="dealer-block-wrapper">
   <div class="container">
       <div class="row">
            <div class="col-12">
                <div class="dealer-block">
                    <div class="left-part">
                        <div class="dealer-logo-block">
                            <div class="dealer-logo">
                                <img src="<?= $dealer->logo ?>" alt="">
                            </div>
                            <div class="dealer-badge">
                                <svg class="icon" width="13" height="13">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-partner"></use>
                                </svg>
                                <?= $translator->translate("{appName} partner", ['appName' => $applicationParameters->getName()]) ?>
                            </div>
                        </div>
                        <h2><?= $dealer->name ?></h2>
                        <div class="dealer-info">
                            <div class="address">
                                <span><?= $dealer->address ?></span>
                                <?php if (!empty($dealer->googleMapsBusinessUrl)) { ?>
                                    <a href="<?= $dealer->googleMapsBusinessUrl ?>" target="_blank"><?= $translator->translate("Map & directions") ?></a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="dealer-info">
                            <?php if (!empty($dealer->reviewsRating) && !empty($dealer->reviewsCount)) { ?>
                                <div class="rating">
                                    <img src="/images/theme/full-star.svg" alt="">
                                    <h3><?= number_format($dealer->reviewsRating, 1) ?></h3>
                                    <a href="<?= $dealer->googleMapsReviewsUrl ?>" target="_blank">
                                        <?php if ($dealer->reviewsCount == 1) { ?>
                                            <?= $translator->translate("1 review") ?>
                                        <?php } else { ?>
                                            <?= $translator->translate("{numberOfReviews} reviews", ['numberOfReviews' => $dealer->reviewsCount]) ?>
                                        <?php } ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <a href="tel: <?= $dealer->phone ?>" class="phone">
                                <svg class="icon" width="16" height="16">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-phone"></use>
                                </svg>
                                <?= $dealer->phone ?>
                            </a>
                            <?php if ($dealer->website) { ?>
                            <a href="<?= $dealer->website ?>" class="web" target="_blank">
                                <svg class="icon" width="16" height="16">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-web"></use>
                                </svg>
                                <?= $dealer->website ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="right-part">
                        <div class="rewards-images">
                            <img src="/images/temp/r1.png" alt="">
                            <img src="/images/temp/r2.png" alt="">
                        </div>
                        <div class="socials-links">
                            <a href="#" target="_blank">
                                <svg class="icon" width="9" height="16">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-fb"></use>
                                </svg>
                            </a>
                            <a href="#" target="_blank">
                                <svg class="icon" width="14" height="14">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-in"></use>
                                </svg>
                            </a>
                            <a href="#" target="_blank">
                                <svg class="icon" width="14" height="11">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-x"></use>
                                </svg>
                            </a>
                            <a href="#" target="_blank">
                                <svg class="icon" width="14" height="14">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-ld"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
       </div>
   </div>
</div>
