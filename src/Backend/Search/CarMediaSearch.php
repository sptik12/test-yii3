<?php

namespace App\Backend\Search;

use Yiisoft\ActiveRecord\ActiveQuery;

final class CarMediaSearch extends AbstractSearch
{
    protected function getDefaultOrder(): array
    {
        return ['carMedia.created' => SORT_ASC];
    }
}
