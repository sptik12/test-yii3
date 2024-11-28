<?php

namespace App\Backend\Model;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Helper\DbStringHelper;
use Yiisoft\Strings\Inflector;

abstract class AbstractModel extends ActiveRecord
{
    public function getTableName(): string
    {
        return self::tableName();
    }

    public static function tableName(): string
    {
        $modelName = DbStringHelper::baseName(static::class);
        $lastPos = strrpos($modelName, 'Model');
        $tableName = substr_replace($modelName, "", $lastPos, strlen("Model"));
        $tableName = (new Inflector())->toCamelCase($tableName);

        return "{{%{$tableName}}}";
    }

    public static function find(): ActiveQuery
    {
        $query = new ActiveQuery(static::class);

        return $query;
    }

    public static function findOne(mixed $condition): array|null|ActiveRecord
    {
        $query = new ActiveQuery(static::class);

        return $query->findOne($condition);
    }

    public static function deleteAllRecords(mixed $condition)
    {
        $className = static::class;
        $model = new $className();
        $model->deleteAll($condition);
    }

    public static function updateAllRecords(array $attributes, mixed $condition)
    {
        $className = static::class;
        $model = new $className();
        $model->updateAll($attributes, $condition);
    }
}
