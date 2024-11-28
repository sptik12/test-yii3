<?php

namespace App\Backend\Service;

use App\Backend\Component\Oauth\OauthSocial;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\User\Provider;
use App\Backend\Model\User\UserModel;
use App\Backend\Model\User\UserAuthorizationCodeModel;
use App\Backend\Model\User\UserOauthModel;
use App\Backend\Model\User\Role;
use App\Backend\Component\Notificator\Notificator;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Translator\TranslatorInterface;

final class AuthorizationService extends AbstractService
{
    public function __construct(
        protected UserService $userService,
        protected CurrentUser $currentUser,
        protected Injector $injector,
    ) {
        parent::__construct($injector);
    }

    /**
     * Find
     */
    public function findUserOauthByIdentifier(string $identifier, Provider $provider): ?UserOauthModel
    {
        return UserOauthModel::findOne(['identifier' => $identifier, 'provider' => $provider->value]);
    }

    /**
     * Methods
     */
    protected function signIn(
        string $email,
        string $password,
        IdentityRepositoryInterface $identityRepository,
    ): string {
        $user = $identityRepository->findByEmail($email);
        $this->currentUser->login($user);

        $this->setCurrentDealerId($user);

        return $this->getRedirectPageByRole();
    }

    protected function sendCode(
        string $email,
        TranslatorInterface $translator,
        ConfigInterface $config,
        Notificator $notificator,
    ): bool {
        $code = rand(100000, 999999);
        $codeKeepAlive = $config->get('params-web')['auth']['сodeKeepAlive'];
        $this->saveUserAuthorizationCode($email, $code, $codeKeepAlive);
        $fromEmail = $config->get('params')['app']['defaultFromEmail'];
        $appName =  $config->get('params')['app']['name'];
        $notificator->push(
            from: $fromEmail,
            to: $email,
            subject: $appName . " " . $translator->translate("Authentication Code"),
            content: [
                'template' => "authenication-code",
                'vars' => [
                    'code' => $code
                ],
            ],
            event: "Send Authorization Code",
            lang: $translator->getLocale()
        );

        return true;
    }

    protected function signInByCode(
        string $email,
        int $code,
        IdentityRepositoryInterface $identityRepository,
    ): string {
        $user = $identityRepository->findByEmail($email);
        $this->currentUser->login($user);

        $this->setCurrentDealerId($user);

        return $this->getRedirectPageByRole();
    }

    protected function signUp(
        string $email,
        ?string $username = null,
        ?string $password = null
    ): bool {
        $user = $this->userService->createUserAndAssignToRole(
            email: $email,
            role: Role::Client,
            username: $username,
            password: $password,
        );

        return $this->currentUser->login($user);
    }

    protected function logout(): bool
    {
        return $this->currentUser->logout();
    }

    protected function signInSocialFromArray(
        array $requestData,
        ConfigInterface $config,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator
    ): string {
        $code = $requestData["code"];
        $socialProvider = $requestData["socialProvider"];
        $oauthSocial = new OauthSocial($socialProvider, $config, $session);

        if (empty($requestData["code"])) {
            return $oauthSocial->getAuthorizationUrl();
        }

        $authInfo = $oauthSocial->getAuthorizationData($code);
        $oAuth = $this->findUserOauthByIdentifier($authInfo->identifier, $socialProvider);
        $user = UserModel::findOne($oAuth ? $oAuth->userId : ['email' => $authInfo->email]);

        if (!$user) {
            $user = $this->userService->createUserAndAssignToRole(
                email: $authInfo->email,
                role: Role::Client,
            );
        }

        if (!$oAuth) {
            $this->createUserOauth($user->id, $authInfo->identifier, $socialProvider);
        }

        $this->currentUser->login($user);

        return $urlGenerator->generate("client.home");
    }

    protected function signUpDealershipFromArray(
        array $requestData,
        DealerService $dealerService,
        UserService $userService,
        GeoService $geoService,
        Notificator $notificator,
        ConfigInterface $config,
        TranslatorInterface $translator
    ): bool {
        $requestDataDealership = $requestData["requestDataDealership"];
        $requestDataUser = $requestData["requestDataUser"];

        // create dealer with non approved status
        $requestDataDealership["status"] = DealerStatus::New->value;

        // assign dealer to random account manager
        $accountManagersIds = $userService->getAccountManagersIds();
        $randomIndex = rand(0, count($accountManagersIds) - 1);
        $requestDataDealership["accountManagerId"] = $accountManagersIds[$randomIndex];

        $dealerModel = $dealerService->createDealer($requestDataDealership);
        $geoService->setDealerGeoData($dealerModel->id);

        // create dealer owner and assign to dealer role
        $requestDataUser["dealerId"] = $dealerModel->id;
        $requestDataUser["role"] = Role::DealerPrimary;
        $user = $userService->createUserAndAssignToDealer($requestDataUser);
        $geoService->setUserGeoData($user->id);

        // login just created user
        $this->currentUser->login($user);
        $this->setCurrentDealerId($user, $dealerModel->id);

        // send notifications to superadmins and assigned account manager
        $superAdminsIds = $userService->getSuperAdminsIds();
        $adminsIds = array_merge($superAdminsIds, [$accountManagersIds[$randomIndex]]);
        $admins = $userService->searchUsersByIds(userIds: $adminsIds, filters: ['active' => true]);
        $fromEmail = $config->get('params')['app']['defaultFromEmail'];
        $appName =  $config->get('params')['app']['name'];
        $requestDataDealership = (object)$requestDataDealership;
        $requestDataUser = (object)$requestDataUser;

        foreach ($admins as $admin) {
            $notificator->push(
                from: $fromEmail,
                to: $admin->email,
                subject: $appName . " " . $translator->translate("New Dealer registration"),
                content: [
                    'template' => "dealer-sign-up",
                    'vars' => [
                        'dealershipName' => $requestDataDealership->name,
                        'businessNumber' => $requestDataDealership->businessNumber,
                        'dealershipAddress' => $requestDataDealership->address,
                        'dealershipProvince' => $requestDataDealership->province,
                        'dealershipPostalCode' => $requestDataDealership->postalCode,
                        'webSite' => $requestDataDealership->website,
                        'username' => $requestDataUser->username,
                        'email' => $requestDataUser->email,
                        'representativeAddress' => $requestDataUser->address,
                        'representativeProvince' => $requestDataUser->province,
                        'representativePostalCode' => $requestDataUser->postalCode,
                        'phone' => $requestDataUser->phone,
                        'licenseNumber' => $requestDataUser->licenseNumber,
                        'dealerId' => $dealerModel->id,
                    ],
                ],
                event: "New Dealer registration",
                lang: $translator->getLocale()
            );
        }

        return true;
    }

    protected function loginAsDealer(
        int $id,
        UrlGeneratorInterface $urlGenerator
    ): string {
        $userModel = $this->userService->findById($this->currentUser->getId());
        $this->setCurrentDealerId($userModel, $id);

        return $urlGenerator->generateAbsolute("dealer.searchCar");
    }





    private function getRedirectPageByRole(): string
    {
        if ($this->userService->isAccountManagerAdmin($this->currentUser)) {
            return "admin.home";
        } elseif ($this->userService->isDealer($this->currentUser)) {
            return "dealer.searchCar";
        } else {
            return "client.searchCar";
        }
    }

    private function saveUserAuthorizationCode(
        string $email,
        int $code,
        int $codeKeepAlive
    ): ?UserAuthorizationCodeModel {
        $user = $this->userService->findByEmail($email);

        if ($user) {
            UserAuthorizationCodeModel::deleteAllRecords(
                [
                    "AND",
                    ['userId' => $user->id],
                    new Expression("DATE_ADD(created, INTERVAL {$codeKeepAlive} MINUTE) < NOW()")
                ]
            );
            $model = new UserAuthorizationCodeModel();
            $model->userId = $user->id;
            $model->code = $code;
            $model->save();
        }

        return $model ?? null;
    }

    private function createUserOauth(
        int $userId,
        string $identifier,
        Provider $provider
    ) {
        $model = new UserOauthModel();
        $model->userId = $userId;
        $model->identifier = $identifier;
        $model->provider = $provider->value;
        $model->save();
    }

    private function setCurrentDealerId(
        UserModel $model,
        ?int $dealerId = null
    ): void {
        if ($this->userService->isDealerOnly($this->currentUser->getId())) {
            $dealerId = $this->userService->findFirstUserDealerPosition($model->id)?->dealerId;
        }

        if ($model->currentDealerId != $dealerId) {
            $model->currentDealerId = $dealerId;
            $model->save();
        }
    }
}
