<?php

namespace App\Backend\Service;

use App\Backend\Component\DataTableRequest;
use App\Backend\Model\AbstractModel;
use App\Backend\Search\AbstractSearch;
use Yiisoft\Injector\Injector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

abstract class AbstractService
{
    public function __construct(
        protected Injector $injector,
        protected ?ViewRenderer $viewRenderer = null,
    ) {
    }





    /**
     * DI autowiring for protected service methods
     *
     * @param string $name
     * @param array $attributes
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function __call(string $name, array $attributes): mixed
    {
        $class = get_called_class();
        $checkVisibility = new \ReflectionMethod($class, $name);

        if ($checkVisibility->isPrivate()) {
            throw new \BadMethodCallException("Call to private method {$class}::{$name}() from global scope");
        }

        $attributes = $this->nameAttributes($name, $attributes);
        $attributes = $this->validate($name, $attributes);

        return $this->injector->invoke($this->{$name}(...), $attributes);
    }





    protected function isFileImage(?string $mimeType): bool
    {
        return $mimeType ? StringHelper::startsWith($mimeType, 'image') : false;
    }

    protected function isFileVideo(?string $mimeType): bool
    {
        return $mimeType ? StringHelper::startsWith($mimeType, 'video') || ($mimeType == "application/octet-stream") : false;
    }

    protected function hydrateModelToObject(AbstractModel $model): object
    {
        return (object)$model->getOldAttributes();
    }

    protected function mergeObjects(object $object1, object $object2): object
    {
        return (object)array_merge(
            json_decode((string)json_encode($object1), true),
            json_decode((string)json_encode($object2), true),
        );
    }



    /* Data Tables */
    protected function tableData(
        AbstractSearch $search,
        DataTableRequest $dataTableRequest,
        array $joinsWith = [],
        array $baseFilters = [],
        null|callable|array $fields = null,
        ?callable $hydrator = null,
        bool $asArray = false
    ): object {
        $draw = $dataTableRequest->drawIndex();
        $columns = $dataTableRequest->columns();
        $filters = array_merge($baseFilters, $dataTableRequest->filters() ?? []);
        $fields = is_callable($fields) ? $fields($columns) : $fields ?? $columns;
        $entries = $search->search(
            fields: $fields,
            filters: $filters,
            joinsWith: $joinsWith,
            sort: $dataTableRequest->sort(),
            perPage: $dataTableRequest->perPage(),
            offset: $dataTableRequest->offset(),
            asArray: $asArray
        );
        $recordsTotal = $search->getTotalRecords(filters: $baseFilters, joinsWith: $joinsWith);
        $recordsFiltered = ($filters) ? $search->getTotalRecords(filters: $filters, joinsWith: $joinsWith) : $recordsTotal;
        $entries = $hydrator ? array_map($hydrator, $entries) : $entries;
        $data = array_map(function ($entry) use ($columns) {
            $entryData = [];

            foreach ($columns as $columnName) {
                $entryData[] = $entry->{$columnName};
            }

            return $entryData;
        }, $entries);

        return (object)compact("draw", "recordsTotal", "recordsFiltered", "data");
    }

    protected function renderTableColumn(string $view, array $params = []): string
    {
        return $this->viewRenderer->renderPartialAsString($view, $params);
    }





    private function nameAttributes(string $name, array $attributes): array
    {
        if (count($attributes)) {
            $attributesKeys = array_keys($attributes);
            $isAllArgumentsAreNamed = !count(
                array_filter(
                    $attributesKeys,
                    fn($attributeName) => is_numeric($attributeName),
                ),
            );

            if (!$isAllArgumentsAreNamed) {
                $reflection = new \ReflectionFunction($this->{$name}(...));
                $methods = $reflection->getParameters();

                foreach ($attributesKeys as $key => $attributeKey) {
                    if (is_numeric($attributeKey) && !empty($methods[$key])) {
                        $attributes[$methods[$key]->name] = $attributes[$key];
                        unset($attributes[$key]);
                    }
                }
            }
        }

        return $attributes;
    }

    private function validate(string $name, array $attributes): array
    {
        $className = get_class($this);
        $className = str_replace("App\\Backend\\Service\\", "", $className);
        $className = str_replace("Service", "", $className);
        $validatorClass = "\App\Backend\Validator\\{$className}Validator";

        if (method_exists($validatorClass, $name)) {
            $validator = $this->injector->make($validatorClass);
            $attributes = $this->injector->invoke($validator->{$name}(...), $attributes);
        }

        return $attributes;
    }
}
