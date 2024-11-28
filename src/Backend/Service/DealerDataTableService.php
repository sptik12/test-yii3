<?php

namespace App\Backend\Service;

use App\Backend\Component\DataTableRequest;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;
use App\Backend\Search\DealerSearch;
use App\Frontend\Helper\FormatHelper;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class DealerDataTableService extends AbstractService
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected Injector $injector,
        ?ViewRenderer $viewRenderer,
    ) {
        parent::__construct($injector, $viewRenderer);
    }





    protected function prepareAdminTableData(
        array $tableRequestData,
        array $baseFilters,
        DealerSearch $dealerSearch,
        CurrentUser $currentUser
    ): object {
        $dataTableRequest = DataTableRequest::fromArray($tableRequestData);
        $tableResponseData = $this->tableData(
            search: $dealerSearch,
            dataTableRequest: $dataTableRequest,
            joinsWith: ['accountManager'],
            baseFilters: $baseFilters,
            fields: $this->prepareFieldsForAdminDataTable(...),
            hydrator: fn($dealerModel) => $this->hydrateToAdminDataTableEntry($dealerModel, $currentUser)
        );

        return $tableResponseData;
    }





    private function prepareFieldsForAdminDataTable(array $columns): array
    {
        foreach ($columns as $key => $name) {
            switch ($name) {
                case "geo":
                    $columns[$key] = "dealer.longitude";
                    $columns[] = "dealer.latitude";
                    break;

                case "action":
                    $columns[$key] = "dealer.status";
                    break;

                case "accountManagerName":
                    $columns[$key] = "dealer.accountManagerId";
                    break;

                case "status":
                    $columns[$key] = "dealer.status";
                    break;

                case "name":
                    $columns[$key] = "dealer.name";
                    break;

                default:
                    $columns[$key] = "dealer.{$name}";
                    break;
            }
        }

        return $columns;
    }

    private function hydrateToAdminDataTableEntry(DealerModel $dealerModel, CurrentUser $currentUser): object
    {
        $dealer = $this->hydrateModelToObject($dealerModel);
        $dealer->originalData = clone $dealer;
        $dealer->geo = !empty($dealer->longitude) ? "{$dealer->longitude}, {$dealer->latitude}" : null;
        $dealer->created = FormatHelper::formatDateShort($dealer->created, $this->config);
        $dealer->province = Province::tryFromName($dealer?->province)?->title($this->translator);
        $dealer->status = DealerStatus::tryFrom($dealer->status)?->title($this->translator);

        $dealer->approveDealerUrl = $this->urlGenerator->generateAbsolute("admin.doApproveDealerAjax");
        $dealer->suspendDealerUrl = $this->urlGenerator->generateAbsolute("admin.suspendDealerAjax");
        $dealer->unsuspendDealerUrl = $this->urlGenerator->generateAbsolute("admin.unsuspendDealerAjax");
        $dealer->loginAsDealerUrl = $this->urlGenerator->generateAbsolute("admin.loginAsDealerAjax", [
            '_language' => $this->translator->getLocale(),
            'id' => $dealer->id
        ]);
        $dealer->editDealerUrl = $this->urlGenerator->generateAbsolute("admin.editDealer", [
            '_language' => $this->translator->getLocale(),
            'id' => $dealer->id
        ]);

        $dealer->canUpdateDealer = $currentUser->can("updateDealer");
        $dealer->canApproveDealer = $currentUser->can("approveDealer");
        $dealer->canSuspendDealer = $currentUser->can("suspendDealer");
        $dealer->canUnsuspendDealer = $currentUser->can("unsuspendDealer");

        // actions
        $dealer->name = $this->renderTableColumn("admin/dealer/columns/name-column", compact("dealer"));
        $dealer->action = $this->renderTableColumn("admin/dealer/columns/action-column", compact("dealer"));

        return $dealer;
    }
}
