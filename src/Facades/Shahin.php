<?php

namespace Mrmr7\LaravelShahin\Facades;

use Illuminate\Support\Facades\Facade;
use Mrmr7\LaravelShahin\Services\Account\AccountService;
use Mrmr7\LaravelShahin\Services\Token\TokenService;
use Mrmr7\LaravelShahin\ShahinManager;

/**
 * @method AccountService account()
 * @method TokenService auth()
 * @method string forUser(string|int $userIdentifier, array $credentials)
 * @method string|int getUserIdentifier()
 * @method array getUserCredentials()
 * @method string bank()
 * @method ShahinManager setBank($bank)
 *
 * @mixin ShahinManager
 *
 * @see \Mrmr7\LaravelShahin\ShahinManager
 */
class Shahin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'MrMr7/LaravelShahin/Shahin';
    }
}
