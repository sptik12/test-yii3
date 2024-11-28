<?php

namespace App\Backend\Model\User;

use AllowDynamicProperties;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\RbacAssignmentModel;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Security\Random;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;

#[AllowDynamicProperties]
final class UserModel extends \App\Backend\Model\AbstractModel implements CookieLoginIdentityInterface
{
    public $roles;
    public $isAdmin;

    /*
     * IdentityInterface method
     */
    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    /*
     * CookieLoginIdentityInterface methods
     */
    public function getCookieLoginKey(): string
    {
        return $this->authKey;
    }

    public function validateCookieLoginKey(string $key): bool
    {
        return $this->authKey === $key;
    }





    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'rbacAssignments' => $this->hasMany(
                RbacAssignmentModel::class,
                [
                    'user_id' => 'id',
                ]
            ),
            'userDealerPosition' => $this->hasOne(
                UserDealerPositionModel::class,
                [
                    'userId' => 'id'
                ]
            ),
            'currentUserDealerPosition' => $this->hasOne(
                UserDealerPositionModel::class,
                [
                    'dealerId' => 'currentDealerId',
                    'userId' => 'id'
                ]
            ),
            'currentDealer' => $this->hasOne(
                DealerModel::class,
                [
                    'id' => 'currentDealerId',
                ]
            ),
            'accountManagerDealers' => $this->hasMany(
                DealerModel::class,
                [
                    'accountManagerId' => 'id',
                ]
            ),

            default => parent::relationQuery($name),
        };
    }

    public function getUserDealerPosition(): ?UserDealerPositionModel
    {
        return $this->relation('userDealerPosition');
    }

    public function getCurrentUserDealerPosition(): ?UserDealerPositionModel
    {
        return $this->relation('currentUserDealerPosition');
    }

    public function getCurrentDealer(): ?DealerModel
    {
        return $this->relation('currentDealer');
    }

    public function getRbacAssignments(): ?array
    {
        return $this->relation('rbacAssignments');
    }

    public function getAccountManagerDealers(): ?array
    {
        return $this->relation('accountManagerDealers');
    }
}
