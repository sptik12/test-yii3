<?php

namespace App\Backend\Search;

use App\Backend\Model\AbstractModel;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Helper\DbStringHelper;
use Yiisoft\Db\Query\Query;

abstract class AbstractSearch
{
    public function search(
        array $fields = [],
        array $filters = [],
        array $joinsWith = [],
        array|string|null $sort = null,
        ?int $perPage = null,
        int $offset = 0,
        bool $asArray = false
    ): array {
        if (!method_exists(static::class, "find")) {
            throw new \LogicException("find() method is not defined in search service", 422);
        }

        $query = $this->find();

        if (!empty($fields)) {
            $query->addSelect($fields);
        }

        if ($filters) {
            $query = $this->applyFilters($query, $filters);
        }

        if ($joinsWith) {
            $query = $this->applyJoins($query, $joinsWith);
        }

        if (!$sort && method_exists(static::class, "getDefaultOrder")) {
            $sort = $this->getDefaultOrder();
        }

        if ($sort) {
            $query = $this->applySorting($query, $sort);
        }

        if (!empty($perPage)) {
            $query->offset($offset)->limit($perPage);
        }

        if ($asArray) {
            return array_map(fn($item) => (object)$item, $query->createCommand()->queryAll());
        } else {
            return $query->all();
        }
    }

    public function searchOne(
        array $fields = [],
        array $filters = [],
        array $joinsWith = [],
    ): ?AbstractModel {
        $results = $this->search(fields: $fields, filters: $filters, joinsWith: $joinsWith);

        return $results ? $results[0] : null;
    }

    public function getTotalRecords(
        array $filters = [],
        array $joinsWith = []
    ): int|string {
        if (!method_exists(static::class, "find")) {
            throw new \LogicException("find() method is not defined in search service", 422);
        }

        $query = $this->find();

        if ($filters) {
            $query = $this->applyFilters($query, $filters);
        }

        if ($joinsWith) {
            $query = $this->applyJoins($query, $joinsWith);
        }

        return $query->count();
    }


    public function applyFilters(ActiveQuery $query, array $filters): ActiveQuery
    {
        foreach ($filters as $filterName => $values) {
            $method = "filter" . ucfirst($filterName);

            if (method_exists(static::class, $method)) {
                $query = $this->{$method}($query, $values);
            } else {
                $query = $this->applyDefaultFilter($query, $filterName, $values);
            }
        }

        return $query;
    }

    public function applyJoins(ActiveQuery $query, array $joins): ActiveQuery
    {
        foreach ($joins as $key => $value) {
            $withValue = !is_numeric($key);
            $joinName = $withValue ? $key : $value;
            $method = "join" . ucfirst($joinName);

            if (method_exists(static::class, $method)) {
                if ($withValue) {
                    $query = $this->{$method}($query, $value);
                } else {
                    $query = $this->{$method}($query);
                }
            }
        }

        return $query;
    }

    public function applySorting(ActiveQuery $query, array|string $sort): ActiveQuery
    {
        if (!is_array($sort)) {
            list($sortColumn, $sortOrder) = $this->extractSortColumnAndOrder($sort);
            $method = "sortBy" . ucfirst($sortColumn);

            if (method_exists(static::class, $method)) {
                $query = $this->{$method}($query, $sortColumn, $sortOrder);
            } else {
                $query->addOrderBy($sort);
            }
        }

        return $query;
    }





    protected static function modelClass(): string
    {
        $baseClassName = DbStringHelper::baseName(static::class);
        $modelName = $baseClassName == "CarSearchUrlSearch" ? "CarSearchUrl" : str_replace("Search", "", $baseClassName);

        $baseModel = match ($modelName) {
            'CarMake' => 'Car',
            'CarModel' => 'Car',
            'CarSearchUrl' => 'Car',
            'UserDealerPosition' => 'User',
            default => $modelName
        };

        return "\App\Backend\Model\\{$baseModel}\\{$modelName}Model";
    }

    protected function find(): ActiveQuery
    {
        return self::modelClass()::find();
    }

    protected function extractSortColumnAndOrder(string $sort): array
    {
        $sortParts = explode(" ", $sort);
        $sortColumn = $sortParts[0];
        $sortOrder = $sortParts[1] ?? null;
        $sortOrder = strtolower($sortOrder) == "desc" ? SORT_DESC : SORT_ASC;

        return [$sortColumn, $sortOrder];
    }





    private function applyDefaultFilter(ActiveQuery $query, string $filterName, mixed $value): ActiveQuery
    {
        if ($value) {
            $tableName = self::modelClass()::tableName();
            $query->andWhere(["{$tableName}.{$filterName}" => $value]);
        }

        return $query;
    }
}
