<?php

use App\Backend\Middleware\AuthorizedOnly;
use App\Backend\Middleware\ExceptionFormatter;
use App\Backend\Middleware\TrimRequestData;
use App\Backend\Middleware\AccessPermissionChecker;
use App\Frontend\Controller\Client;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create("/{_language}")
        ->host($_ENV['CLIENT_HOST'])
        ->middleware(Authentication::class)
        ->middleware(AuthorizedOnly::class)
        ->middleware(TrimRequestData::class)
        ->middleware(ExceptionFormatter::class)
        ->routes(
            Route::get("/")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\MainController::class, 'index'])
                ->name("client.home"),
            Route::get("/session")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\MainController::class, 'session'])
                ->name("client.session"),


            /**
             * Wishlist (allowed for authorized users only)
             */
            Route::get("/car/wishlist")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->action([Client\CarController::class, 'wishlist'])
                ->name("client.wishlist"),

            /* Ajax */
            Route::get("/car/wishlist-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'wishlistAjax'])
                ->name("client.wishlistAjax"),
            Route::post("/car/add-car-to-wishlist-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'addCarToWishlistAjax'])
                ->name("client.addCarToWishlistAjax"),
            Route::post("/car/remove-car-from-wishlist-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'removeCarFromWishlistAjax'])
                ->name("client.removeCarFromWishlistAjax"),

            /**
             * Cars search urls (allowed for authorized users only)
             */
            Route::get("/car/car-search-urls")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->action([Client\CarController::class, 'carSearchUrls'])
                ->name("client.carSearchUrls"),

            /* Ajax */
            Route::get("/car/car-search-urls-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->action([Client\CarController::class, 'carSearchUrlsAjax'])
                ->name("client.carSearchUrlsAjax"),
            Route::post("/car/add-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'addCarSearchUrlAjax'])
                ->name("client.addCarSearchUrlAjax"),
            Route::post("/car/update-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'updateCarSearchUrlAjax'])
                ->name("client.updateCarSearchUrlAjax"),
            Route::post("/car/remove-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'removeCarSearchUrlAjax'])
                ->name("client.removeCarSearchUrlAjax"),
            Route::post("/car/delete-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'deleteCarSearchUrlAjax'])
                ->name("client.deleteCarSearchUrlAjax"),
            Route::post("/car/restore-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'restoreCarSearchUrlAjax'])
                ->name("client.restoreCarSearchUrlAjax"),
            Route::post("/check-car-search-url-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'checkCarSearchUrlAjax'])
                ->name("client.checkCarSearchUrlAjax"),


            /**
             * Search car
             */
            Route::get("/car/search")
                ->disableMiddleware(AuthorizedOnly::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->action([Client\CarController::class, 'search'])
                ->name("client.searchCar"),

            /* Ajax */
            Route::get("/car/search-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('searchCar'))
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\CarController::class, 'searchAjax'])
                ->name("client.searchCarAjax"),
            Route::post("/car/get-models-for-view-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\CarController::class, 'getModelsForViewAjax'])
                ->name("client.getModelsForViewAjax"),
            Route::post("/car/set-geo-data-for-postal-code-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\CarController::class, 'setGeoDataForPostalCodeAjax'])
                ->name("client.setGeoDataForPostalCodeAjax"),
            Route::get("/car/get-postal-code-by-geo-data-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\CarController::class, 'getPostalCodeByGeoDataAjax'])
                ->name("client.getPostalCodeByGeoDataAjax"),



            /**
             * My Cars
             */
            Route::get("/car/mycars")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Client\CarController::class, 'myCars'])
                ->name("client.myCars"),

            /* Ajax */
            Route::get("/car/mycars-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Client\CarController::class, 'myCarsAjax'])
                ->name("client.myCarsAjax"),

            /**
             * Add car
             */
            /* Pages */
            Route::get("/car/add-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Client\CarController::class, 'addCar'])
                ->name("client.addCar"),

            /* Handlers */
            Route::post("/car/do-add-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Client\CarController::class, 'doAddCar'])
                ->name("client.doAddCar"),
            Route::post("/car/do-add-empty-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('addCar'))
                ->action([Client\CarController::class, 'doAddEmptyCar'])
                ->name("client.doAddEmptyCar"),

            /**
             * View car
             */
            Route::get("/car/{publicId:\w+}")
                ->disableMiddleware(AuthorizedOnly::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewCar'))
                ->action([Client\CarController::class, 'view'])
                ->name("client.viewCar"),


            /**
             * Preview car
             */
            Route::get("/car/preview/{publicId:\w+}")
                ->disableMiddleware(AuthorizedOnly::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewCar'))
                ->action([Client\CarController::class, 'preview'])
                ->name("client.previewCar"),

            /* Ajax */
            Route::post("/car/do-save-draft-car-from-preview-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'doSaveDraftCarFromPreviewAjax'])
                ->name("client.doSaveDraftCarFromPreviewAjax"),
            Route::post("/car/update-preview-car-session-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'updatePreviewCarSessionAjax'])
                ->name("client.updatePreviewCarSessionAjax"),
            Route::post("/car/restore-preview-car-session-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'restorePreviewCarSessionAjax'])
                ->name("client.restorePreviewCarSessionAjax"),

            /**
             * Edit car
             */
            /* Pages */
            Route::get("/car/edit-car/{publicId:\w+}")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->action([Client\CarController::class, 'editCar'])
                ->name("client.editCar"),

            /* Handlers */
            Route::post("/car/do-save-draft-car")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->action([Client\CarController::class, 'doSaveDraftCar'])
                ->name("client.doSaveDraftCar"),

            /* Ajax */
            Route::post("/car/get-vin-code-data-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'getVinCodeDataAjax'])
                ->name("client.getVinCodeDataAjax"),
            Route::post("/car/get-models-for-edit-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\CarController::class, 'getModelsForEditAjax'])
                ->name("client.getModelsForEditAjax"),
            Route::post("/car/delete-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'deleteMediaAjax'])
                ->name("client.deleteMediaAjax"),
            Route::post("/car/set-media-main-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'setMediaMainAjax'])
                ->name("client.setMediaMainAjax"),
            Route::post("/car/upload-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'uploadMediaAjax'])
                ->name("client.uploadMediaAjax"),
            Route::post("/car/sort-media-ajax")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'sortMediaAjax'])
                ->name("client.sortMediaAjax"),


            // tmp routes, client will not be able to publish cars himself!!!
            Route::post("/car/do-publish-car-ajax")
                // ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publishCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'doPublishCarAjax'])
                ->name("client.doPublishCarAjax"),
            Route::post("/car/do-publish-car-from-preview-ajax")
                // ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publishCar'))
                ->middleware(FormatDataResponseAsJson::class)
                ->action([Client\CarController::class, 'doPublishCarFromPreviewAjax'])
                ->name("client.doPublishCarFromPreviewAjax"),


            /**
             * Profile
             */
            Route::get("/profile")
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('updateProfile'))
                ->action([Client\ProfileController::class, 'profile'])
                ->name("client.profile"),

            /**
             * Authorization
             */
            /* Pages */
            Route::get("/sign-in")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'signIn'])
                ->name("client.signIn"),
            Route::get("/sign-up")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'signUp'])
                ->name("client.signUp"),
            Route::get("/sign-up-dealership")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'signUpDealership'])
                ->name("client.signUpDealership"),


            /* Handlers */
            Route::post("/do-sign-in")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'doSignIn'])
                ->name("client.doSignIn"),
            Route::post("/do-sign-up")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'doSignUp'])
                ->name("client.doSignUp"),
            Route::post("/do-sign-up-dealership")
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'doSignUpDealership'])
                ->name("client.doSignUpDealership"),
            Route::get("/sign-in-social")
                ->disableMiddleware(AuthorizedOnly::class)
                ->disableMiddleware(ExceptionFormatter::class)
                ->action([Client\AuthorizationController::class, 'doSignInSocial'])
                ->name("client.signInSocial"),
            Route::get("/logout")
                ->action([Client\AuthorizationController::class, 'doLogout'])
                ->name("client.logout"),


            /* Ajax */
            Route::post("/send-code-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'sendCodeAjax'])
                ->name("client.sendCodeAjax"),
            Route::post("/sign-in-by-code-ajax")
                ->middleware(FormatDataResponseAsJson::class)
                ->disableMiddleware(AuthorizedOnly::class)
                ->action([Client\AuthorizationController::class, 'signInByCodeAjax'])
                ->name("client.signInByCodeAjax"),
        ),
];
