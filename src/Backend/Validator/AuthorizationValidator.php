<?php

namespace App\Backend\Validator;

use App\Backend\Model\User\Status;
use App\Backend\Model\User\UserAuthorizationCodeModel;
use App\Backend\Model\User\UserModel;
use App\Backend\Model\User\Role;
use App\Backend\Model\User\Provider;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Service\DealerService;
use App\Backend\Service\GeoService;
use App\Backend\Service\UserService;
use Yiisoft\Session\SessionInterface;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Security\PasswordHasher;
use Yiisoft\User\CurrentUser;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\InEnum;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\Rule\Url;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Rbac\Manager;

final class AuthorizationValidator extends AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected UserService $userService
    ) {
        parent::__construct(translator: $translator);
    }

    public function signIn(
        string $email,
        string $password,
        ConfigInterface $config
    ): array {
        $data = compact("email", "password");

        /* Check general rules */
        $this->validateData($data, [
            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],
            'password' => [
                new Required(),
            ],
        ]);

        /* Check specific rules */
        $user = $this->userService->findByEmail($data['email']);

        if (!$user) {
            $this->throwValidationException("email", $this->translator->translate("User with email {email} not found", ['email' => $data['email']]));
        }

        if ($user->status != Status::Active->value) {
            $supportEmail =  $config->get('params')['app']['supportEmail'];
            $this->throwValidationException(
                "email",
                $this->translator->translate("Your are suspended.If you have any questions about the suspension, please, contact site Administration by email {email}", ["email" => $supportEmail])
            );
        }

        if (!$this->validatePassword($user, $data['password'])) {
            $this->throwValidationException("password", $this->translator->translate("Incorrect password"));
        }

        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    public function sendCode(
        string $email,
        ConfigInterface $config
    ): array {
        $data = compact("email");

        /* Check general rules */
        $this->validateData($data, [
            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $user = $this->userService->findByEmail($data['email']);

        if (!$user) {
            $this->throwValidationException("email", $this->translator->translate("User with email {email} not found", ['email' => $data['email']]));
        }

        if ($user->status != Status::Active->value) {
            $supportEmail =  $config->get('params')['app']['supportEmail'];
            $this->throwValidationException(
                "email",
                $this->translator->translate("Your are suspended.If you have any questions about the suspension, please, contact site Administration by email {email}", ["email" => $supportEmail])
            );
        }

        return [
            'email' => $data['email'],
        ];
    }

    public function signInByCode(
        string $email,
        int $code,
        ConfigInterface $config,
        Manager $manager,
    ): array {
        $data = compact("email", "code");

        /* Check general rules */
        $this->validateData($data, [
            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],
            'code' => [
                new Required(),
                new Integer(skipOnError: true)
            ],
        ]);

        /* Check specific rules */
        $user = $this->userService->findByEmail($data['email']);

        if (!$user) {
            $this->throwValidationException("email", $this->translator->translate("User with email {email} not found", ['email' => $data['email']]));
        }


        if ($user->status != Status::Active->value) {
            $supportEmail =  $config->get('params')['app']['supportEmail'];
            $this->throwValidationException(
                "email",
                $this->translator->translate("Your are suspended.If you have any questions about the suspension, please, contact site Administration by email {email}", ["email" => $supportEmail])
            );
        }

        $codeKeepAlive = $config->get('params-web')['auth']['сodeKeepAlive'];

        if (!$this->checkUserAuthorizationCode($user->id, $data['code'], $codeKeepAlive)) {
            $this->throwValidationException("code", $this->translator->translate("You have entered invalid or expired code"));
        }

        if ($manager->userHasPermission($user->id, Role::AdminSuperAdmin->value)) {
            $this->throwValidationException("code", $this->translator->translate("Permission denied"), 404);
        }

        return [
            'email' => $data['email'],
            'code' => $data['code'],
        ];
    }

    public function signUp(
        string $email,
        ?string $username = null,
        ?string $password = null
    ): array {
        $data = compact("email", "username", "password");

        /* Check general rules */
        $this->validateData($data, [
            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],
            'username' => [
                $this->getUserNameValidator()
            ],
            'password' => [
                $this->getUserPasswordValidator()
            ],
        ]);

        /* Check specific rules */
        $user = $this->userService->findByEmail($data['email']);

        if ($user) {
            $this->throwValidationException("email", $this->translator->translate("A user with this email is already registered"));
        }

        return [
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    public function signUpDealershipFromArray(
        array $requestData,
        UserService $userService,
        GeoService $geoService
    ): array {
        return $this->validateCreateDealership($requestData, $userService, $geoService);
    }

    public function signInSocialFromArray(
        array $requestData,
        SessionInterface $session,
    ): array {
        $requestData = $this->fetchOptional($requestData, ["code", "state", "socialProvider"]);

        /* Check general rules */
        $this->validateData($requestData, [
            'socialProvider' => [
                new InEnum(
                    class: Provider::class,
                    skipOnEmpty: true
                ),
            ],
        ]);

        /* Check specific rules */
        $requestData['socialProvider'] = !empty($requestData["socialProvider"])
            ? Provider::from($requestData["socialProvider"])
            : Provider::Google;

        if (!empty($requestData["code"])) {
            $oAuthState = $session->get("{$requestData["socialProvider"]->value}OauthState");

            if ($oAuthState != $requestData["state"]) {
                $this->throwValidationException("state", "Invalid state");
            }
        }

        return compact("requestData");
    }

    public function loginAsDealer(
        int $id,
        DealerService $dealerService,
        CurrentUser $currentUser
    ) {
        $this->validateData(["id" => $id], [
            'id' => [
                new Integer()
            ],
        ]);

        $this->validateCanManageDealer($id, $dealerService, $this->userService, $currentUser);

        return compact("id");
    }





    private function validatePassword(UserModel $user, string $password): bool
    {
        return (new PasswordHasher())->validate($password, $user->passwordHash);
    }

    private function checkUserAuthorizationCode(int $userId, int $code, int $codeKeepAlive): bool
    {
        $query = new ActiveQuery(UserAuthorizationCodeModel::class);

        return $query
            ->where(['userId' => $userId, 'code' => $code])
            ->andWhere(new Expression("DATE_ADD(created, INTERVAL {$codeKeepAlive} MINUTE) > NOW()"))
            ->exists();
    }
}
