<?php

namespace App\Backend\Service;

use App\Backend\Component\Notificator\Notificator;
use App\Backend\Model\Car\CarSearchUrlModel;
use App\Backend\Model\Car\CarUserModel;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\RbacAssignmentModel;
use App\Backend\Model\User\UserModel;
use App\Backend\Model\User\UserDealerPositionModel;
use App\Backend\Model\User\Status;
use App\Backend\Model\User\Role;
use App\Backend\Model\User\UserOauthModel;
use App\Backend\Model\Province;
use App\Backend\Search\UserSearch;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Security\Random;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Security\PasswordHasher;
use Yiisoft\User\CurrentUser;
use Yiisoft\Rbac\Manager;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Html\Html;
use Psr\Log\LoggerInterface;

final class UserService extends AbstractService implements IdentityRepositoryInterface
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected Manager $manager,
        protected GeoService $geoService,
        protected UserDealerPositionService $userDealerPositionService,
        protected Injector $injector
    ) {
        parent::__construct($injector);
    }

    /*
     * Roles/Permissions
     */

    public function isSuperAdmin(CurrentUser $currentUser): bool
    {
        return $currentUser->can(Role::AdminSuperAdmin->value);
    }

    // super admin || account manager
    public function isAccountManagerAdmin(CurrentUser $currentUser): bool
    {
        return $currentUser->can(Role::AdminAccountManager->value);
    }


    public function isClient(CurrentUser $currentUser): bool
    {
        return $currentUser->can(Role::Client->value);
    }

    public function isDealer(CurrentUser $currentUser): bool
    {
        return $currentUser->can(Role::DealerSalesManager->value);
    }




    // parent roles are not taken into account for functions below
    // =============================

    public function isAccountManagerAdminOnly(int $userId): bool
    {
        $roles = $this->getUserRoles($userId);

        return array_intersect([Role::AdminAccountManager->value], $roles) ? true : false;
    }

    // dealer.primary || dealer.salesManager
    public function isDealerOnly(int $userId): bool
    {
        $roles = $this->getUserRoles($userId);

        return array_intersect([Role::DealerPrimary->value, Role::DealerSalesManager->value], $roles) ? true : false;
    }

    // this is the replacement of $this->manager->getRolesByUserId without taken in account of the parent roles
    public function getUserRoles(int $userId): array
    {
        return RbacAssignmentModel::find()
            ->select(['rbacAssignment.item_name'])
            ->joinWith(['rbacItem'])
            ->where(['rbacAssignment.user_id' => $userId, 'rbacItem.type' => 'role'])
            ->column();
    }

    public function getCurrentUserRoles(
        CurrentUser $currentUser,
    ): array {
        $roles = [];

        if ($currentUser->isGuest()) {
            $roles[] = $this->manager->getGuestRole();
        } else {
            $roles = $this->getUserRoles($currentUser->getId());
        }

        return $roles;
    }

    // this is the replacement of $this->manager->getUserIdsByRoleName without taken in account of the parent roles
    public function getUserIdsByRoleName(string|array $roleName)
    {
        return RbacAssignmentModel::find()->select(['user_id'])->where(['item_name' => $roleName])->column();
    }

    public function getSuperAdminsIds(): array
    {
        return $this->getUserIdsByRoleName(Role::AdminSuperAdmin->value);
    }

    public function getAccountManagersIds(): array
    {
        return $this->getUserIdsByRoleName(Role::AdminAccountManager->value);
    }

    // =============================



    public function getPossibleStatuses(): array
    {
        $res = [];

        foreach (Status::cases() as $status) {
            if ($status != Status::Deleted) {
                $res[] = $status;
            }
        }

        return $res;
    }

    public function getPossibleRoles(): array
    {
        $res = [];

        foreach (Role::cases() as $role) {
            $res[] = $role;
        }

        return $res;
    }

    /*
     * IdentityRepositoryInterface
     */
    public function findIdentity(string $id): ?UserModel
    {
        return UserModel::findOne($id);
    }






    /**
     * Find
     */
    public function findById(int $id): ?UserModel
    {
        return UserModel::findOne($id);
    }

    public function findByEmail(string $email): ?UserModel
    {
        return UserModel::findOne(['email' => $email]);
    }

    public function existsByEmail(string $email, int $excludeUserId = 0): bool
    {
        return UserModel::find()->where(['email' => $email])->andFilterWhere(['<>', 'id', $excludeUserId])->exists();
    }

    public function findUserDealerPosition(int $userId, int $dealerId): ?UserDealerPositionModel
    {
        return UserDealerPositionModel::findOne(['userId' => $userId, 'dealerId' => $dealerId]);
    }

    public function findFirstUserDealerPosition(int $userId): ?UserDealerPositionModel
    {
        return UserDealerPositionModel::findOne(['userId' => $userId]);
    }

    public function isUserAssignedToSomeDealer(int $userId, string $role): bool
    {
        return UserDealerPositionModel::find()->where(['userId' => $userId, 'role' => $role])->exists();
    }

    /**
     * Search
     */
    protected function searchUserRolesData(
        int $userId,
        UserSearch $userSearch
    ): array {
        $items = $userSearch->search(
            fields: ["user.id"],
            filters: ["id" => $userId],
            joinsWith: ['roles']  // joinWith("rbacAssignments.userDealerPositions.dealer")
        );

        foreach ($items as &$item) {
            $item = $this->hydrateModelToObject($item);
        }

        return $items;
    }

    protected function searchUserWithDealerRole(
        int $userId,
        int $dealerId,
        UserSearch $userSearch
    ): object {
        $item = $userSearch->searchOne(
            fields: ["user.*"],
            filters: ["id" => $userId],
            joinsWith: ['userDealerPosition' => $dealerId]
        );

        if ($item) {
            $item = $this->hydrateToUserCard($item);
        }

        return $item;
    }

    protected function searchUsersByIds(
        array $userIds,
        ?array $filters = [],
        UserSearch $userSearch
    ): array {
        $defaultFilters = ['id' => $userIds];
        $filters = array_merge($defaultFilters, $filters ?? []);
        $admins = $userSearch->search(
            fields: ["user.id", "user.email", "user.username", "user.status", "user.created"],
            filters: $filters
        );

        foreach ($admins as &$admin) {
            $admin = $this->hydrateModelToObject($admin);
        }

        return $admins;
    }

    protected function searchUsers(
        array $filters,
        UserSearch $userSearch
    ): object {
        $items = [];
        $filters = array_merge($filters, ["active" => true]);
        $totalCount = $userSearch->getTotalRecords(
            filters: $filters,
        );

        if ($totalCount) {
            $items = $userSearch->search(
                fields: ["user.*"],
                filters: $filters,
                joinsWith: ['roles'],
            );

            foreach ($items as &$user) {
                $user = $this->hydrateToListCard($user);
            }
        }

        return (object)compact("items", "totalCount");
    }



    /**
     * Methods with validation
     */
    protected function getUser(
        int $id,
    ): object {
        $user = $this->findById($id);
        $user = $this->hydrateToUserCard($user);

        return $user;
    }


    protected function addUserFromArray(
        array $requestData
    ): ?UserModel {
        $requestDataObject = (object)$requestData;

        if ($requestDataObject->role->isDealerRole()) {
            return $this->createUserAndAssignToDealer($requestData);
        } else {
            $userModel = $this->createUserAndAssignToRole(
                email: $requestDataObject->email,
                role: $requestDataObject->role,
                username: $requestDataObject->username
            );

            return $this->updateUserDetails($requestData, $userModel);
        }
    }

    protected function updateUserFromArray(
        array $requestData
    ): ?UserModel {
        $requestDataObject = (object)$requestData;
        $user = $this->findById($requestDataObject->id);
        $user->username = $requestDataObject->username;
        $user->email = $requestDataObject->email;
        $user->save();

        $user = $this->updateUserDetails($requestData, $user);

        return $user;
    }

    protected function addRoleToUserFromArray(array $requestData): object
    {
        $requestDataObject = (object)$requestData;
        $this->assignUserToRole($requestDataObject->userId, $requestDataObject->role);

        if ($requestDataObject->role->isDealerRole()) {
            if ($requestDataObject->role == Role::DealerPrimary) {
                $this->setAllPrimaryDealersToSalesManagers($requestDataObject->dealerId);
            }
            $this->assignUserToDealer($requestDataObject->userId, $requestDataObject->dealerId, $requestDataObject->role);
        }

        $userModel = $this->findById($requestDataObject->userId);
        $userModel = $this->updateUserDetails($requestData, $userModel);

        return $this->hydrateToUserCard($userModel);
    }

    protected function unassignRoleFromUserFromArray(array $requestData): object
    {
        $requestDataObject = (object)$requestData;

        if ($requestDataObject->role->isDealerRole()) {
            $this->unassignUserFromDealerAndRoleIfNeeded($requestDataObject->userId, $requestDataObject->dealerId);
        } else {
            $this->manager->revoke($requestDataObject->role->value, $requestDataObject->userId);
        }

        return $this->getUser($requestDataObject->userId);
    }


    protected function addUserToDealerFromArray(array $requestData): ?UserModel
    {
        return $this->createUserAndAssignToDealer($requestData);
    }

    protected function updateUserToDealerFromArray(
        array $requestData
    ): ?UserModel {
        $requestDataObject = (object)$requestData;
        $user = $this->findById($requestDataObject->id);
        $user->username = $requestDataObject->username;
        $user->email = $requestDataObject->email;
        $user->save();

        $user = $this->updateUserDetails($requestData, $user);

        if ($requestDataObject->role == Role::DealerPrimary) {
            $this->setAllPrimaryDealersToSalesManagers($requestDataObject->dealerId);
        }

        $this->unassignUserFromDealerAndRoleIfNeeded($requestDataObject->id, $requestDataObject->dealerId);
        $this->assignUserToRole($requestDataObject->id, $requestDataObject->role);
        $this->assignUserToDealer($requestDataObject->id, $requestDataObject->dealerId, $requestDataObject->role);

        return $user;
    }

    protected function unassignUserFromDealerFromArray(array $requestData)
    {
        $requestData = (object)$requestData;
        $this->unassignUserFromDealerAndRoleIfNeeded($requestData->userId, $requestData->dealerId);
    }

    protected function setUserAsPrimaryDealerFromArray(array $requestData)
    {
        $requestData = (object)$requestData;

        $this->setAllPrimaryDealersToSalesManagers($requestData->dealerId);

        $this->unassignUserFromDealerAndRoleIfNeeded($requestData->userId, $requestData->dealerId);
        $this->assignUserToRole($requestData->userId, Role::DealerPrimary);
        $this->assignUserToDealer($requestData->userId, $requestData->dealerId, Role::DealerPrimary);
    }

    protected function validateDeleteUserFromArray(
        array $requestData,
        CarService $carService,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService
    ): array {
        $requestData = (object)$requestData;
        $userId = $requestData->userId;

        return $this->validateDeleteUser($userId, $carService, $dealerService, $userDealerPositionService);
    }

    protected function deleteUserFromArray(
        array $requestData,
        CarService $carService,
        ConnectionInterface $db,
        Aliases $aliases,
        LoggerInterface $logger
    ) {
        $requestData = (object)$requestData;
        $userId = $requestData->userId;
        $this->deleteUser($userId, $carService, $db, $aliases, $logger);
    }

    protected function setUserDeletionDateFromArray(
        array $requestData,
        ConfigInterface $config
    ) {
        $requestData = (object)$requestData;
        $userId = $requestData->userId;
        $this->setUserDeletionDate($userId, $config);
    }

    protected function clearUserDeletionDateFromArray(
        array $requestData,
    ) {
        $requestData = (object)$requestData;
        $userId = $requestData->userId;
        $this->clearUserDeletionDate($userId);
    }

    protected function unsuspendUserFromArray(
        array $requestData
    ) {
        $requestData = (object)$requestData;
        $this->unsuspendUser($requestData->userId);
    }

    protected function suspendUserFromArray(
        array $requestData,
        ConfigInterface $config,
        Notificator $notificator,
    ) {
        $requestData = (object)$requestData;
        $this->suspendUser($requestData->userId, $requestData->notifyUser, $config, $notificator);
    }


    /**
     * Other methods
     */

    protected function createUserAndAssignToRole(
        string $email,
        Role $role,
        ?string $username = null,
        ?string $password = null
    ): ?UserModel {
        $user = new UserModel();
        $user->email = $email;
        $user->username = !empty($username) ? $username : $this->createUserNameFromEmail($email);
        $user->authKey = Random::string(32);
        $password = empty($password) ? hash("sha256", rand()) : $password;
        $user->passwordHash = (new PasswordHasher())->hash($password);
        $user->currentDealerId = null;
        $user->save();

        $this->assignUserToRole($user->id, $role);

        return $user;
    }

    protected function createUserAndAssignToDealer(
        array $data
    ): ?UserModel {
        $requestDataObject = (object)$data;
        $userModel = $this->createUserAndAssignToRole(
            email: $requestDataObject->email,
            role: $requestDataObject->role,
            username: $requestDataObject->username
        );
        $userModel = $this->updateUserDetails($data, $userModel);

        if ($requestDataObject->role == Role::DealerPrimary) {
            $this->setAllPrimaryDealersToSalesManagers($requestDataObject->dealerId);
        }

        $this->assignUserToDealer($userModel->id, $requestDataObject->dealerId, $requestDataObject->role);

        return $userModel;
    }

    protected function assignUserToRole(
        int $userId,
        Role $role
    ) {
        $this->manager->assign($role->value, $userId);
    }

    protected function assignUserToDealer(
        int $userId,
        int $dealerId,
        Role $role
    ): ?UserDealerPositionModel {
        $userDealerPositionModel = $this->findUserDealerPosition($userId, $dealerId);

        if (!$userDealerPositionModel) {
            $userDealerPositionModel = new UserDealerPositionModel();
            $userDealerPositionModel->userId = $userId;
            $userDealerPositionModel->dealerId = $dealerId;
            $userDealerPositionModel->role = $role->value;
        } else {
            $userDealerPositionModel->role = $role->value;
        }

        $userDealerPositionModel->save();

        return $userDealerPositionModel;
    }

    protected function unassignUserFromDealerAndRoleIfNeeded(
        int $userId,
        int $dealerId
    ): void {
        $userDealerPositionModel = $this->findUserDealerPosition($userId, $dealerId);

        if ($userDealerPositionModel) {
            $role = $userDealerPositionModel->role;

            // unassign user from dealer
            UserDealerPositionModel::deleteAllRecords(['userId' => $userId, 'dealerId' => $dealerId]);

            // clear currentDealerId field
            UserModel::updateAllRecords(['currentDealerId' => null], ['id' => $userId, 'currentDealerId' => $dealerId]);

            // user can have the same role in another dealer, so we check whether we can revoke it from rbac
            if (!$this->isUserAssignedToSomeDealer($userId, $role)) {
                $this->manager->revoke($role, $userId);
            }
        }
    }

    protected function setAllPrimaryDealersToSalesManagers(
        int $dealerId
    ): void {
        $userDealerPositions = $this->userDealerPositionService->search(filters: ["dealerId" => $dealerId]);

        foreach ($userDealerPositions as $userDealerPosition) {
            if ($userDealerPosition->role == Role::DealerPrimary->value) {
                $this->unassignUserFromDealerAndRoleIfNeeded($userDealerPosition->userId, $dealerId);
                $this->assignUserToRole($userDealerPosition->userId, Role::DealerSalesManager);
                $this->assignUserToDealer($userDealerPosition->userId, $dealerId, Role::DealerSalesManager);
            }
        }
    }

    protected function updateUserDetails(
        array $data,
        UserModel $userModel
    ): ?UserModel {
        $data = (object)$data;

        if (property_exists($data, 'licenseNumber')) {
            $userModel->licenseNumber = $data->licenseNumber;
        }

        if (property_exists($data, 'address')) {
            $userModel->address = $data->address;
        }

        if (property_exists($data, 'province')) {
            $userModel->province = $data->province;
        }

        if (property_exists($data, 'postalCode')) {
            $userModel->postalCode = $data->postalCode;
        }

        if (property_exists($data, 'phone')) {
            $userModel->phone = $data->phone;
        }

        if (property_exists($data, 'receiveEmails')) {
            $userModel->receiveEmails = $data->receiveEmails;
        }

        if (property_exists($data, 'customComission')) {
            $userModel->customComission = $data->customComission;
        }
        $userModel->save();

        $userModel = $this->geoService->setUserGeoData($userModel->id);

        return $userModel;
    }

    protected function validateDeleteUser(
        int $userId,
        CarService $carService,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService,
    ): array {
        $messages = [];

        if ($this->isAccountManagerAdminOnly($userId)) {
            $messages = $this->validateDeleteAccountManager($userId, $dealerService);
        }

        if ($this->isDealerOnly($userId)) {
            $messages = $this->validateDeleteDealer($userId, $userDealerPositionService);
        }

        $countCars = $carService->getClientCarsCount($userId);

        if ($countCars > 0) {
            $messages[] = $this->translator->translate(
                "You cannot remove this user because of {countCars} car(s) in his catalog",
                ["countCars" => $countCars]
            );
        }

        return [
            "messages" => $messages
        ];
    }

    protected function deleteUser(
        int $userId,
        CarService $carService,
        ConnectionInterface $db,
        Aliases $aliases,
        LoggerInterface $logger
    ): bool {
        $carService->deleteClientCars($userId);

        $result = true;
        $transaction = $db->beginTransaction();

        try {
            $this->manager->revokeAll($userId);
            UserDealerPositionModel::deleteAllRecords(['userId' => $userId]);
            UserOauthModel::deleteAllRecords(['userId' => $userId]);
            CarUserModel::deleteAllRecords(["userId" => $userId]);
            CarSearchUrlModel::deleteAllRecords(["userId" => $userId]);
            UserModel::deleteAllRecords(['id' => $userId]);
            $transaction->commit();
        } catch (\Throwable $e) {
            $result = false;
            $transaction->rollBack();
            $logger->error($e);
        }

        return $result;
    }

    protected function suspendUser(
        int $userId,
        bool $notifyUser,
        ConfigInterface $config,
        Notificator $notificator,
    ) {
        $userModel = $this->findById($userId);
        $userModel->updateAttributes(['status' => Status::Disabled->value]);

        if ($notifyUser) {
            $fromEmail = $config->get('params')['app']['defaultFromEmail'];
            $appName =  $config->get('params')['app']['name'];
            $notificator->push(
                from: $fromEmail,
                to: $userModel->email,
                subject: $appName . " " . $this->translator->translate("user suspension"),
                content: [
                    'template' => "user-suspended",
                    'vars' => [
                        "username" => $userModel->username
                    ],
                ],
                event: "User Suspended",
                lang: $this->translator->getLocale()
            );
        }
    }

    protected function removeUsersByDeletedDate(
        CarService $carService,
        ConnectionInterface $db,
        Aliases $aliases,
        LoggerInterface $logger
    ) {
        $userIds = UserModel::find()->select(["user.id"])->where("user.deletionDate < NOW()")->column();

        foreach ($userIds as $userId) {
            $this->deleteUser(
                $userId,
                $carService,
                $db,
                $aliases,
                $logger
            );
        }
    }


    protected function setUserDeletionDate(
        int $userId,
        ConfigInterface $config
    ) {
        $userModel = $this->findById($userId);
        $days =  $config->get('params')['settings']['deferredUserDeletionDays'];
        $userModel->updateAttributes(['deletionDate' => date('Y-m-d', strtotime(" + {$days} days"))]);
    }

    protected function clearUserDeletionDate(
        int $userId
    ) {
        $userModel = $this->findById($userId);
        $userModel->updateAttributes(['deletionDate' => null]);
    }

    protected function unsuspendUser(
        int $userId
    ) {
        $userModel = $this->findById($userId);
        $userModel->updateAttributes(['status' => Status::Active->value]);
    }





    private function createUserNameFromEmail(string $email): string
    {
        $arr = explode("@", $email);
        $username = ucwords(str_replace(['.', '-', '_'], ' ', $arr[0]));

        return $username;
    }

    private function hydrateToListCard(
        UserModel $userModel
    ): object {
        $user = $this->hydrateModelToObject($userModel);
        $user->roles = explode(",", $user->roles);
        $user->rolesList = implode(", ", $user->roles);
        $user->isSuperAdmin = in_array(Role::AdminSuperAdmin->value, $user->roles);
        $user->deleteUrl = $this->urlGenerator->generateAbsolute("admin.deleteUserAjax");

        return $user;
    }

    private function hydrateToUserCard(
        UserModel $userModel
    ): object {
        $user = $this->hydrateModelToObject($userModel);
        $userProvince = Province::tryFromName($user?->province)?->title($this->translator);
        $user->fullAddress = "{$user->address}, {$userProvince} {$user->postalCode}";
        $user->receiveEmails = $user->receiveEmails ? true : false;
        $user->roles = $this->getUserRoles($userModel->id);
        $user->isSuperAdmin = in_array(Role::AdminSuperAdmin->value, $user->roles);
        $user->isAccountManager = in_array(Role::AdminAccountManager->value, $user->roles);
        $user->isDealer = array_intersect([Role::DealerPrimary->value, Role::DealerSalesManager->value], $user->roles) ? true : false;

        return $user;
    }

    private function validateDeleteAccountManager(int $userId, DealerService $dealerService): array
    {
        $messages = [];
        $accountManagersIds = $this->getAccountManagersIds();
        $isLastAccountManager = count($accountManagersIds) == 1;

        if ($isLastAccountManager) {
            $messages[] = $this->translator->translate("You are trying to remove last Account Manager in the system.");
        }

        $dealers = $dealerService->searchDealers(filters: ['accountManager' => $userId]);

        if ($dealers->totalCount) {
            $dealers = $dealers->items;
            $editDealerLinks = array_map([$this, "generateEditDealerLink"], $dealers);
            $editDealerLinks = implode("<br> ", $editDealerLinks);
            $messages[] =
                $this->translator->translate(
                    "You cannot remove this Account Manager because of {countDealers} dealership(s) assigned to him.",
                    ['countDealers' => count($dealers)]
                )
                . Html::tag('br')
                . $this->translator->translate("Dealerships list") . ":"
                . Html::tag('br')
                . $editDealerLinks;
        }

        return $messages;
    }

    private function validateDeleteDealer(int $userId, UserDealerPositionService $userDealerPositionService): array
    {
        $messages = [];
        $dealersWithLastMember = $userDealerPositionService->getUserDealershipsWithLastMember($userId);

        if ($dealersWithLastMember) {
            $editDealerLinks = array_map([$this, "generateEditDealerLink"], $dealersWithLastMember);
            $editDealerLinks = implode("<br> ", $editDealerLinks);
            $messages[] =
                $this->translator->translate(
                    "You cannot remove this Dealer because of {countDealers} dealership(s) assigned to him.",
                    ['countDealers' => count($dealersWithLastMember)]
                )
                . Html::tag('br')
                . $this->translator->translate("Dealerships list") . ":"
                . Html::tag('br')
                . $editDealerLinks;
        }

        return $messages;
    }

    private function generateEditDealerLink(object $dealer): string
    {
        $editDealerUrl = $this->urlGenerator->generateAbsolute("admin.editDealer", ['id' => $dealer->id]);

        return Html::a($dealer->name, $editDealerUrl);
    }
}
