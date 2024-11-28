<?php

declare(strict_types=1);

namespace App\Frontend\ViewInjection;

use App\Backend\Component\CurrentPanel;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Service\CarService;
use App\Backend\Service\CarSearchUrlService;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\I18n\Locale;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

final class LayoutViewInjection implements LayoutParametersInjectionInterface
{
    public function __construct(
        private Aliases $aliases,
        private AssetManager $assetManager,
        private Locale $locale,
        private CurrentRoute $currentRoute,
        private CurrentUser $currentUser,
        private CarService $carService,
        private CarSearchUrlService $carSearchUrlService
    ) {
    }

    public function getLayoutParameters(): array
    {
        $currentPanel = new CurrentPanel($this->currentRoute);
        $displayNotApprovedMessage = false;

        $userId =  $this->currentUser->getId();
        $currentDealerId = $userId ? $this->currentUser->getIdentity()->currentDealerId : null;

        if ($currentPanel->getId() == "dealer") {
            $currentDealer = $this->currentUser->getIdentity()->getCurrentDealer();
            $displayNotApprovedMessage = $currentDealer?->status != DealerStatus::Active->value;
        }

        if ($currentPanel->getId() == "client") {
            $userId = $this->currentUser->getId();
            $carUsersCount = ($userId) ? $this->carService->getCarUserCount((int)$userId) : 0;
            $carSearchUrlsCount = ($userId) ? $this->carSearchUrlService->getCarSearchUrlsCount((int)$userId) : 0;
        }

        return [
            'aliases' => $this->aliases,
            'assetManager' => $this->assetManager,
            'locale' => $this->locale,
            'currentRoute' => $this->currentRoute,
            'currentUser' => $this->currentUser,
            'userId' => $userId,
            'currentDealerId' => $currentDealerId,
            'displayNotApprovedMessage' => $displayNotApprovedMessage,
            'carUsersCount' => $carUsersCount ?? 0,
            'carSearchUrlsCount' => $carSearchUrlsCount ?? 0
        ];
    }
}
