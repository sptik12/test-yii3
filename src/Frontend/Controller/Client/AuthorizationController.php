<?php

namespace App\Frontend\Controller\Client;

use App\Backend\Service\AuthorizationService;
use App\Backend\Service\NotyService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use App\Backend\Component\CookieLogin;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class AuthorizationController extends AbstractClientController
{
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct(
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            urlGenerator: $urlGenerator,
            layout: "auth",
        );
    }

    /**
     * Pages
     */
    public function signIn(ServerRequestInterface $request): ResponseInterface
    {
        $requestData = $this->getRequestData($request);
        $returnUrl = !empty($requestData["returnUrl"]) ? $requestData["returnUrl"] : null;

        return $this->viewRenderer->render("sign-in", compact("returnUrl"));
    }

    public function signUp(): ResponseInterface
    {
        return $this->viewRenderer->render("sign-up");
    }

    public function signUpDealership(): ResponseInterface
    {
        return $this->viewRenderer->render("sign-up-dealership");
    }





    /**
     * Handlers
     */
    public function doSignIn(
        AuthorizationService $authorizationService,
        CookieLogin $cookieLogin,
        CurrentUser $currentUser,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $email = $requestData["email"];
        $password = $requestData["password"];
        $redirectPage = $authorizationService->signIn($email, $password);
        $response = !empty($requestData['returnUrl'])
            ? $this->redirect($requestData['returnUrl'])
            : $this->redirectByNameAbsolute($redirectPage);

        return $cookieLogin->addCookie($currentUser->getIdentity(), $response);
    }

    public function doSignUp(
        AuthorizationService $authorizationService,
        CookieLogin $cookieLogin,
        CurrentUser $currentUser,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $email = $requestData["email"];
        $username = $requestData["username"];
        $password = $requestData["password"];
        $authorizationService->signUp($email, $username, $password);
        $response = $this->redirectByName("client.searchCar");

        return $cookieLogin->addCookie($currentUser->getIdentity(), $response);
    }

    public function doSignUpDealership(
        AuthorizationService $authorizationService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $authorizationService->signUpDealershipFromArray($requestData);

        return $this->redirectByNameAbsolute("dealer.searchCar");
    }

    public function doSignInSocial(
        AuthorizationService $authorizationService,
        ServerRequestInterface $request,
        NotyService $noty,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): ResponseInterface {
        try {
            $requestData = $this->getRequestData($request);
            $redirectUrl = $authorizationService->signInSocialFromArray($requestData);

            return $this->redirect($redirectUrl);
        } catch (\Throwable $e) {
            $logger->error($e);
            $this->showGeneralNotyError($noty, $translator);

            return $this->redirectByName("client.signIn");
        }
    }

    public function doLogout(
        AuthorizationService $authorizationService,
        CurrentUser $currentUser
    ): ResponseInterface {
        $authorizationService->logout($currentUser);

        return $this->redirectByName("client.signIn");
    }





    /**
     * Ajax
     */
    public function sendCodeAjax(
        AuthorizationService $authorizationService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $email = $requestData["email"];
        $isCodeSent = $authorizationService->sendCode($email);

        return $dataResponseFactory->createResponse(compact("isCodeSent"));
    }

    public function signInByCodeAjax(
        AuthorizationService $authorizationService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $email = $requestData["email"];
        $code = $requestData["code"];
        $redirectPage = $authorizationService->signInByCode($email, $code);
        $redirectUrl = $this->urlGenerator->generateAbsolute(name: $redirectPage);

        return $dataResponseFactory->createResponse(compact("redirectUrl"));
    }
}
