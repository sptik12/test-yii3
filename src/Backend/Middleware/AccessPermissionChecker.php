<?php

declare(strict_types=1);

namespace App\Backend\Middleware;

use App\Backend\Exception\Http\ForbiddenException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\User\CurrentUser;

final class AccessPermissionChecker implements MiddlewareInterface
{
    private ?string $permission = null;

    public function __construct(
        private CurrentUser $currentUser
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->permission === null) {
            throw new InvalidArgumentException('Permission not set.');
        }

        if (!$this->currentUser->can($this->permission)) {
            throw new ForbiddenException();
        }

        // Todo: если нужно будет реализовать алгоритм дополнительной проверки прав дилера
        // (ex: дилер - админ в одном дилершипе и staff - в другом, надо проверять, что там, где он
        // staff, нельзя выполнять действия админа), то алгоритм такой:
        // if ($this->userService->isDealer($this->currentUser)) {
        //  -выдергиваем $role из таблицы userDealerPosition роль юзера в текущем дилершипе
        //  -получаем $permissions = Manager->getPermissionsByRoleName($role)
        //  -смотрим, есть ли $this->permission в списке $permissions
        // }

        return $handler->handle($request);
    }

    public function withPermission(string $permission): self
    {
        $new = clone $this;
        $new->permission = $permission;

        return $new;
    }
}
