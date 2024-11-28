<?php

namespace App\Backend\Model\Dealer;

use AllowDynamicProperties;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use App\Backend\Model\User\UserModel;

#[AllowDynamicProperties]
final class DealerModel extends \App\Backend\Model\AbstractModel
{
    const DEFAULT_IMAGE = "/uploads/dealers/default.png";

    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'accountManager' => $this->hasOne(UserModel::class, ['id' => 'accountManagerId']),
            default => parent::relationQuery($name),
        };
    }

    public function getAccountManager(): ?UserModel
    {
        return $this->relation('accountManager');
    }
}
