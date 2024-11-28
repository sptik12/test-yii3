<?php

namespace App\Backend\Model;

use Yiisoft\ActiveRecord\ActiveRecordInterface;

class AbstractQuery extends \Yiisoft\ActiveRecord\ActiveQuery
{
    /**
     *
     * @return $this
     */
    public function onePopulate(): array|null|ActiveRecordInterface
    {
        return parent::onePopulate();
    }
}
