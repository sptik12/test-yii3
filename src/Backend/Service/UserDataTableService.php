<?php
/**
 * NOTE:
 * This class exists because UserService throws a recursive dependency exception when specifying the ViewRenderer dependency
 */

namespace App\Backend\Service;

use App\Backend\Component\DataTableRequest;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;
use App\Backend\Model\User\Status;
use App\Backend\Model\User\UserModel;
use App\Backend\Search\UserSearch;
use App\Frontend\Helper\Ancillary;
use App\Frontend\Helper\FormatHelper;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class UserDataTableService extends AbstractService
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected UrlGeneratorInterface $urlGenerator,
        protected ConfigInterface $config,
        protected UserService $userService,
        protected UserDealerPositionService $userDealerPositionService,
        protected DealerService $dealerService,
        Injector $injector,
        ?ViewRenderer $viewRenderer = null,
    ) {
        parent::__construct($injector, $viewRenderer);
    }





    protected function prepareAdminTableData(
        array $tableRequestData,
        UserSearch $userSearch,
    ): object {
        $dataTableRequest = DataTableRequest::fromArray($tableRequestData);
        $tableResponseData = $this->tableData(
            search: $userSearch,
            dataTableRequest: $dataTableRequest,
            joinsWith: ['rolesList', 'userDealersList'],
            fields: ["user.id", "user.email", "user.username", "user.created", "user.status", "user.deletionDate"],
            hydrator: $this->hydrateToAdminDataTableEntry(...)
        );

        return $tableResponseData;
    }

    protected function prepareAccountManagersTableData(
        array $tableRequestData,
        UserSearch $userSearch,
    ): object {
        $dataTableRequest = DataTableRequest::fromArray($tableRequestData);
        $tableResponseData = $this->tableData(
            search: $userSearch,
            dataTableRequest: $dataTableRequest,
            joinsWith: ['role' => Role::AdminAccountManager->value],
            fields: ["user.id", "user.email", "user.username", "user.created", "user.status", "user.customComission"],
            hydrator: $this->hydrateToAccountManagersDataTableEntry(...)
        );

        return $tableResponseData;
    }

    protected function prepareDealerTableData(
        int $dealerId,
        array $tableRequestData,
        UserSearch $userSearch,
    ): object {
        $dataTableRequest = DataTableRequest::fromArray($tableRequestData);
        $recordsTotal = $userSearch->getTotalRecords(joinsWith: ['userDealerPosition' => $dealerId]);
        $tableResponseData = $this->tableData(
            search: $userSearch,
            dataTableRequest: $dataTableRequest,
            joinsWith: ['userDealerPosition' => $dealerId],
            fields: $this->prepareFieldsForDealerDataTable(...),
            hydrator: fn($userModel) => $this->hydrateToDealerDataTableEntry($userModel, $recordsTotal)
        );

        return $tableResponseData;
    }

    protected function prepareUserRolesTableData(
        int $userId,
        array $tableRequestData,
        UserSearch $userSearch,
    ): object {
        $dataTableRequest = DataTableRequest::fromArray($tableRequestData);
        $tableResponseData = $this->tableData(
            search: $userSearch,
            dataTableRequest: $dataTableRequest,
            joinsWith: ['roles'],
            baseFilters: ["id" => $userId],
            fields: ["user.id"],
            hydrator: $this->hydrateToUserRolesDataTableEntry(...),
            asArray: true
        );

        return $tableResponseData;
    }





    private function hydrateToAdminDataTableEntry(UserModel $userModel): object
    {
        $user = $this->hydrateModelToObject($userModel);
        $user->originalData = clone $user;
        $user->roles = explode(",", $user->roles);
        $user->rolesList = implode(", ", array_map(fn($role) => Role::tryFrom($role)?->title($this->translator), $user->roles));
        $user->isSuperAdmin = in_array(Role::AdminSuperAdmin->value, $user->roles);
        $user->isAccountManager = in_array(Role::AdminAccountManager->value, $user->roles);
        $user->status = Status::tryFrom($user->status)?->title($this->translator);
        $user->created = FormatHelper::formatDateShort($user->created, $this->config);
        $user->deleteUrl = $this->urlGenerator->generateAbsolute("admin.deleteUserAjax");
        $user->sendOneTimeCodeUrl = $this->urlGenerator->generateAbsolute("admin.sendCodeAjax");
        $user->suspendUserUrl = $this->urlGenerator->generateAbsolute("admin.suspendUserAjax");
        $user->unsuspendUserUrl = $this->urlGenerator->generateAbsolute("admin.unsuspendUserAjax");
        $user->editUrl = $this->urlGenerator->generateAbsolute("admin.editUser", [
            '_language' => $this->translator->getLocale(),
            'id' => $user->id
        ]);

        if ($user->isAccountManager) {
            $dealers = $this->dealerService->searchDealersForList(filters: ['accountManager' => $user->id]);
            $user->dealers = ($user->dealers ?? '') . implode(', ', array_column($dealers, 'name'));
        }

        $user->comments = null;

        if ($user->deletionDate) {
            $user->deletionDate = FormatHelper::formatDate($user->deletionDate, $this->config);
            $user->clearUserDeletionDateUrl = $this->urlGenerator->generateAbsolute("admin.clearUserDeletionDateAjax");
            $user->comments = $this->renderTableColumn("admin/user/columns/comments-column", compact("user"));
        }

        // actions
        $user->username = $this->renderTableColumn("admin/user/columns/name-column", compact("user"));
        $user->action = $this->renderTableColumn("admin/user/columns/action-column", compact("user"));

        return $user;
    }

    private function hydrateToAccountManagersDataTableEntry(UserModel $userModel): object
    {
        $user = $this->hydrateModelToObject($userModel);
        $user->originalData = clone $user;
        $user->status = Status::tryFrom($user->status)?->title($this->translator);
        $user->created = FormatHelper::formatDateShort($user->created, $this->config);
        $user->editUrl = $this->urlGenerator->generateAbsolute("admin.editUser", [
            '_language' => $this->translator->getLocale(),
            'id' => $user->id
        ]);

        $dealers = $this->dealerService->searchDealersForList(filters: ['accountManager' => $user->id]);
        $user->dealers = implode(', ', array_column($dealers, 'name'));
        $user->dealersCount = count($dealers);
        $user->customComission = FormatHelper::formatPercent($user->customComission);

        // actions
        $user->username = $this->renderTableColumn("admin/user/columns/name-column", compact("user"));
        $user->action = "";

        return $user;
    }



    private function prepareFieldsForDealerDataTable(array $columns): array
    {
        foreach ($columns as $key => $name) {
            switch ($name) {
                case "action":
                case "roleName":
                    unset($columns[$key]);
                    break;
                case "fullAddress":
                    $columns[$key] = "address";
                    $columns[] = "province";
                    $columns[] = "postalCode";
                    break;
                case "statusName":
                    $columns[$key] = "user.status";
                    break;
                case "usernameLink":
                    $columns[$key] = "user.username";
                    break;
            }
        }

        return $columns;
    }


    private function hydrateToDealerDataTableEntry(UserModel $userModel, int $recordsTotal): object
    {
        $user = $this->hydrateModelToObject($userModel);
        $user->originalData = clone $user;
        $user->created = FormatHelper::formatDateShort($user->created, $this->config);
        $user->role =  Role::tryFrom($user->role)?->title($this->translator);
        $user->isPrimaryDealer = $user->role == Role::DealerPrimary->value;
        $userProvince = Province::tryFrom($user?->province)?->title($this->translator);
        $user->status = Status::tryFrom($user->status)?->title($this->translator);
        $user->fullAddress = "{$user->address}, {$userProvince} {$user->postalCode}";
        $user->unassignFromDealerUrl = $this->urlGenerator->generateAbsolute("admin.unassignUserFromDealerAjax");
        $user->editUserInDealerUrl = $this->urlGenerator->generateAbsolute("admin.getUserWithDealerPositionAjax", ['id' => $user->id, 'dealerId' => $user->dealerId]);
        $user->setUserAsPrimaryInDealerUrl = $this->urlGenerator->generateAbsolute("admin.setUserAsPrimaryDealerAjax");

        // actions
        $user->username = $this->renderTableColumn("admin/dealer/columns/user-name-column", compact("user"));
        $user->action = $this->renderTableColumn("admin/dealer/columns/user-action-column", compact("user", "recordsTotal"));

        return $user;
    }

    private function hydrateToUserRolesDataTableEntry(object $user): object
    {
        $user->roleName = Role::tryFrom($user->role)?->title($this->translator);
        $user->unassignFromRoleUrl = $this->urlGenerator->generateAbsolute("admin.unassignRoleFromUserAjax");
        $user->canUnassignFromRole = true;

        // account manager role
        if ($user->role == Role::AdminAccountManager->value) {
            $dealers = $this->dealerService->searchDealersForList(filters: ['accountManager' => $user->id]);
            $user->dealer = implode(', ', array_column($dealers, 'name'));

            if (count($dealers)) {
                $user->canUnassignFromRole = false;
            }
        }

        // dealer role
        if ($user->dealerId) {
            if ($this->userDealerPositionService->searchTotal(filters: ["dealer" => $user->dealerId], joinsWith: []) == 1) {
                $user->canUnassignFromRole = false;
            }
        }

        $user->action = $this->renderTableColumn("admin/user/columns/unassign-role-column", compact("user"));

        return $user;
    }
}
