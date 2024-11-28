<?php

namespace App\Backend\Service;

use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Rbac\Manager;
use App\Backend\Search\UserDealerPositionSearch;

final class UserDealerPositionService extends AbstractService
{
    public function __construct(
        protected UserDealerPositionSearch $userDealerPositionSearch,
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected Manager $manager,
        protected Injector $injector
    ) {
    }





    protected function search(
        array $filters = [],
        array $joinsWith = []
    ): array {
        $items = $this->userDealerPositionSearch->search(
            fields: ["userDealerPosition.*"],
            filters: $filters,
            joinsWith: $joinsWith
        );

        foreach ($items as &$item) {
            $item = $this->hydrateModelToObject($item);
        }

        return $items;
    }

    protected function searchTotal(
        array $filters = [],
        array $joinsWith = []
    ): int {
        return $this->userDealerPositionSearch->getTotalRecords(
            filters: $filters,
            joinsWith: $joinsWith,
        );
    }

    protected function getUserDealerships(
        int $userId
    ): array {
        $items = $this->userDealerPositionSearch->search(
            fields: [
                "userDealerPosition.*"
            ],
            filters: ["user" => $userId],
            joinsWith: ["dealer"]
        );

        foreach ($items as &$item) {
            $item = $this->hydrateModelToObject($item);
        }

        return $items;
    }

    protected function getUserDealershipsWithLastMember(
        int $userId
    ): array {
        $items = $this->userDealerPositionSearch->getUserDealershipsWithLastMember($userId);

        foreach ($items as &$item) {
            $item = (object)($item);
        }

        return $items;
    }
}
