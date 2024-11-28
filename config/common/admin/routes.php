<?php

use App\Backend\Middleware\AuthorizedOnly;
use App\Backend\Middleware\ExceptionFormatter;
use App\Backend\Middleware\TrimRequestData;
use App\Backend\Middleware\AccessPanelChecker;
use App\Backend\Middleware\AccessPermissionChecker;
use App\Frontend\Controller\Admin;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;

return [
    Group::create("/{_language}")
        ->host($_ENV['ADMIN_HOST'])
        ->middleware(Authentication::class)
        ->middleware(AuthorizedOnly::class)
        ->middleware(AccessPanelChecker::class)
        ->middleware(TrimRequestData::class)
        ->middleware(ExceptionFormatter::class)
        ->routes(
            Route::get("/")
                ->action([Admin\MainController::class, 'index'])
                ->name("admin.home"),

            /**
             * Users page
             */
            Route::get("/users")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchUser'))
                ->action([Admin\UserController::class, 'index'])
                ->name("admin.users"),

            /* Ajax */
            Route::get("/users/search-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'searchAjax'])
                ->name("admin.searchUsersAjax"),
            Route::post("/users/delete-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'deleteUserAjax'])
                ->name("admin.deleteUserAjax"),
            Route::post("/users/validate-delete-user-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'validateDeleteUserAjax'])
                ->name("admin.validateDeleteUserAjax"),
            Route::post("/users/set-user-deletion-date-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'setUserDeletionDateAjax'])
                ->name("admin.setUserDeletionDateAjax"),
            Route::post("/users/clear-user-deletion-date-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'clearUserDeletionDateAjax'])
                ->name("admin.clearUserDeletionDateAjax"),
            Route::post("/users/send-code-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('sendCodeToUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'sendCodeAjax'])
                ->name("admin.sendCodeAjax"),
            Route::post("/users/suspend-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('suspendUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'suspendUserAjax'])
                ->name("admin.suspendUserAjax"),
            Route::post("/users/unsuspend-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('unsuspendUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'unsuspendUserAjax'])
                ->name("admin.unsuspendUserAjax"),

            /**
             * Add User
             */
            Route::get("/users/add-user")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('createUser'))
                ->action([Admin\UserController::class, 'addUser'])
                ->name("admin.addUser"),

            /* Handlers */
            Route::post("/users/do-add-user")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('createUser'))
                ->action([Admin\UserController::class, 'doAddUser'])
                ->name("admin.doAddUser"),


            /**
             * Edit User
             */
            Route::get("/users/edit-user/{id:\d+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateUser'))
                ->action([Admin\UserController::class, 'editUser'])
                ->name("admin.editUser"),

            /* Ajax */
            Route::post("/users/do-edit-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'doEditUserAjax'])
                ->name("admin.doEditUserAjax"),
            Route::get("/users/search-user-roles-ajax/{id:\d+}")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'searchUserRolesAjax'])
                ->name("admin.searchUserRolesAjax"),
            Route::post("/users/add-role-to-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'addRoleToUserAjax'])
                ->name("admin.addRoleToUserAjax"),
            Route::post("/users/unassign-role-from-user-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateUser'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'unassignRoleFromUserAjax'])
                ->name("admin.unassignRoleFromUserAjax"),
            Route::post("/users/set-user-as-primary-dealer-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'setUserAsPrimaryDealerAjax'])
                ->name("admin.setUserAsPrimaryDealerAjax"),
            Route::get("/users/get-user-ajax/{id:\d+}")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'getUserAjax'])
                ->name("admin.getUserAjax"),
            Route::get("/users/get-user-with-dealer-position-ajax/{id:\d+}/{dealerId:\d+}")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'getUserWithDealerPositionAjax'])
                ->name("admin.getUserWithDealerPositionAjax"),


            /**
             * Account Managers page
             */
            Route::get("/users/account-managers")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchUser'))
                ->action([Admin\UserController::class, 'accountManagers'])
                ->name("admin.accountManagers"),


            /* Ajax */
            Route::get("/users/search-account-managers-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'searchAccountManagersAjax'])
                ->name("admin.searchAccountManagersAjax"),


            /**
             * Dealers page
             */
            Route::get("/dealers")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchDealer'))
                ->action([Admin\DealerController::class, 'index'])
                ->name("admin.dealers"),

            /* Ajax */
            Route::get("/dealers/search-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'searchAjax'])
                ->name("admin.searchDealersAjax"),


            /**
             * Add Dealer
             */
            Route::get("/dealers/add-dealer")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('createDealer'))
                ->action([Admin\DealerController::class, 'addDealer'])
                ->name("admin.addDealer"),

            /* Handlers */
            Route::post("/dealers/do-add-dealer")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('createDealer'))
                ->action([Admin\DealerController::class, 'doAddDealer'])
                ->name("admin.doAddDealer"),

            /**
             * Edit Dealer
             */
            Route::get("/dealers/edit-dealer/{id:\d+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateDealer'))
                ->action([Admin\DealerController::class, 'editDealer'])
                ->name("admin.editDealer"),

            /* Ajax */
            Route::post("/dealers/do-edit-dealer-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'doEditDealerAjax'])
                ->name("admin.doEditDealerAjax"),
            Route::get("/users/search-for-dealer-ajax/{id:\d+}")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'searchForDealerAjax'])
                ->name("admin.searchUsersForDealerAjax"),
            Route::post("/users/unassign-user-from-dealer-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'unassignUserFromDealerAjax'])
                ->name("admin.unassignUserFromDealerAjax"),
            Route::post("/users/add-user-to-dealer-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'addUserToDealerAjax'])
                ->name("admin.addUserToDealerAjax"),
            Route::post("/users/update-user-to-dealer-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\UserController::class, 'updateUserToDealerAjax'])
                ->name("admin.updateUserToDealerAjax"),
            Route::post("/dealers/upload-dealer-logo-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'uploadDealerLogoAjax'])
                ->name("admin.uploadDealerLogoAjax"),
            Route::get("/dealers/delete-dealer-logo-ajax/{id:\d+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'deleteDealerLogoAjax'])
                ->name("admin.deleteDealerLogoAjax"),
            Route::get("/dealers/login-as-dealer-ajax/{id:\d+}")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'loginAsDealerAjax'])
                ->name("admin.loginAsDealerAjax"),
            Route::post("/dealers/assign-account-managers-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('assignAccountManager'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'assignAccountManagersAjax'])
                ->name("admin.assignAccountManagersAjax"),



            /**
             * Approve dealer page
             */
            Route::get("/dealers/approve-dealer/{id:\d+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('approveDealer'))
                ->action([Admin\DealerController::class, 'approveDealer'])
                ->name("admin.approveDealer"),

            /* Ajax */
            Route::post("/dealers/do-approve-dealer-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('approveDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'doApproveDealerAjax'])
                ->name("admin.doApproveDealerAjax"),
            Route::post("/dealers/suspend-dealer-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('suspendDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'suspendDealerAjax'])
                ->name("admin.suspendDealerAjax"),
            Route::post("/dealers/unsuspend-dealer-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('unsuspendDealer'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Admin\DealerController::class, 'unsuspendDealerAjax'])
                ->name("admin.unsuspendDealerAjax"),
        ),
];
