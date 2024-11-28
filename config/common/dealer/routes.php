<?php

use App\Backend\Middleware\AuthorizedOnly;
use App\Backend\Middleware\ExceptionFormatter;
use App\Backend\Middleware\TrimRequestData;
use App\Backend\Middleware\AccessPanelChecker;
use App\Backend\Middleware\AccessPermissionChecker;
use App\Frontend\Controller\Dealer;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create("/{_language}")
        ->host($_ENV['DEALER_HOST'])
        ->middleware(Authentication::class)
        ->middleware(AuthorizedOnly::class)
        ->middleware(AccessPanelChecker::class)
        ->middleware(TrimRequestData::class)
        ->middleware(ExceptionFormatter::class)
        ->routes(
            Route::get("/")
                ->action([Dealer\MainController::class, 'index'])
                ->name("dealer.home"),

            /**
             * Search car
             */
            Route::methods([Method::GET, Method::POST], "/car/search")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->action([Dealer\CarController::class, 'search'])
                ->name("dealer.searchCar"),

            /* Ajax */
            Route::get("/car/search-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Dealer\CarController::class, 'searchAjax'])
                ->name("dealer.searchCarAjax"),
            Route::post("/car/get-models-for-view-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Dealer\CarController::class, 'getModelsForViewAjax'])
                ->name("dealer.getModelsForViewAjax"),


            /**
             * Add car
             */
            /* Pages */
            Route::get("/car/add-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Dealer\CarController::class, 'addCar'])
                ->name("dealer.addCar"),

            /* Handlers */
            Route::post("/car/do-add-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Dealer\CarController::class, 'doAddCar'])
                ->name("dealer.doAddCar"),
            Route::post("/car/do-add-empty-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Dealer\CarController::class, 'doAddEmptyCar'])
                ->name("dealer.doAddEmptyCar"),


            /**
             * View car
             */
            Route::get("/car/{publicId:\w+}")
                ->disableMiddleware(AuthorizedOnly::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewCar'))
                ->action([Dealer\CarController::class, 'view'])
                ->name("dealer.viewCar"),

            /**
             * Preview car
             */
            Route::get("/car/preview/{publicId:\w+}")
                ->disableMiddleware(AuthorizedOnly::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewCar'))
                ->action([Dealer\CarController::class, 'preview'])
                ->name("dealer.previewCar"),

            /* Ajax */
            Route::post("/car/do-publish-car-from-preview-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publishCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'doPublishCarFromPreviewAjax'])
                ->name("dealer.doPublishCarFromPreviewAjax"),
            Route::post("/car/do-save-draft-car-from-preview-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'doSaveDraftCarFromPreviewAjax'])
                ->name("dealer.doSaveDraftCarFromPreviewAjax"),
            Route::post("/car/update-preview-car-session-ajax")
                // ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'updatePreviewCarSessionAjax'])
                ->name("dealer.updatePreviewCarSessionAjax"),
            Route::post("/car/restore-preview-car-session-ajax")
                // ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'restorePreviewCarSessionAjax'])
                ->name("dealer.restorePreviewCarSessionAjax"),

            /**
             * Edit car
             */
            /* Pages */
            Route::get("/car/edit-car/{publicId:\w+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->action([Dealer\CarController::class, 'editCar'])
                ->name("dealer.editCar"),

            /* Handlers */
            Route::post("/car/do-save-draft-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->action([Dealer\CarController::class, 'doSaveDraftCar'])
                ->name("dealer.doSaveDraftCar"),

            /* Ajax */
            Route::post("/car/get-vin-code-data-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'getVinCodeDataAjax'])
                ->name("dealer.getVinCodeDataAjax"),
            Route::post("/car/get-models-for-edit-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Dealer\CarController::class, 'getModelsForEditAjax'])
                ->name("dealer.getModelsForEditAjax"),
            Route::post("/car/delete-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'deleteMediaAjax'])
                ->name("dealer.deleteMediaAjax"),
            Route::post("/car/set-media-main-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'setMediaMainAjax'])
                ->name("dealer.setMediaMainAjax"),
            Route::post("/car/upload-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'uploadMediaAjax'])
                ->name("dealer.uploadMediaAjax"),
            Route::post("/car/sort-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'sortMediaAjax'])
                ->name("dealer.sortMediaAjax"),
            Route::post("/car/do-publish-car-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publishCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Dealer\CarController::class, 'doPublishCarAjax'])
                ->name("dealer.doPublishCarAjax"),
        ),
];
