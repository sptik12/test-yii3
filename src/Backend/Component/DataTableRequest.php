<?php

namespace App\Backend\Component;

final class DataTableRequest
{
    public function __construct(
        private int $drawIndex = 1,
        private ?array $filters = null,
        private ?array $columns = null,
        private ?int $perPage = null,
        private int $offset = 0,
        private ?string $sortColumn = null,
        private ?string $sortOrder = null,
    ) {
    }



    public static function fromArray(array $tableRequestData): self
    {
        $drawIndex = $tableRequestData['draw'] ?? 1;
        $filters = self::extractFilters($tableRequestData);
        $columns = self::extractColumns($tableRequestData);
        list($perPage, $offset) = self::extractPaginationData($tableRequestData);
        list($sortColumn, $sortOrder) = self::extractSortColumnAndOrder($tableRequestData);

        return new DataTableRequest($drawIndex, $filters, $columns, $perPage, $offset, $sortColumn, $sortOrder);
    }



    public function drawIndex(): int
    {
        return $this->drawIndex;
    }

    public function filters(): ?array
    {
        return $this->filters;
    }

    public function columns(): ?array
    {
        return $this->columns;
    }

    public function perPage(): ?int
    {
        return $this->perPage;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function sort(): ?string
    {
        return !empty($this->sortColumn) ? "{$this->sortColumn} {$this->sortOrder}" : null;
    }

    public function sortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function sortOrder(): ?string
    {
        return $this->sortOrder;
    }





    private static function extractFilters(array $tableRequestData): array
    {
        $filters = [];

        // Search
        if (!empty($tableRequestData['search']['value'])) {
            $filters['tableTyped'] = $tableRequestData['search']['value'];
        }

        // Column Search
        foreach ($tableRequestData['columns'] as $column) {
            if (!empty($column['search']['value'])) {
                $filterName = "tableColumn" . ucfirst($column['name']);
                $filters[$filterName] = $column['search']['value'];
            }
        }

        // Custom filters
        if (!empty($tableRequestData['filters'])) {
            foreach ($tableRequestData['filters'] as $filter) {
                $filters[$filter['name']] = $filter['value'];
            }
        }

        return $filters;
    }

    private static function extractColumns(array $tableRequestData): array
    {
        $columns = array_column($tableRequestData['columns'], "name");

        return array_values(array_filter($columns, fn($columnName) => !empty($columnName)));
    }

    private static function extractPaginationData(array $tableRequestData): array
    {
        $perPage = $tableRequestData['length'] ?? null;
        $offset = $tableRequestData['start'] ?? 0;

        return [$perPage, $offset];
    }

    private static function extractSortColumnAndOrder(array $tableRequestData): array
    {
        $sortColumn = $tableRequestData['order'][0]['name'] ?? null;
        $sortOrder = $tableRequestData['order'][0]['dir'] ?? "ASC";

        return [$sortColumn, $sortOrder];
    }
}
