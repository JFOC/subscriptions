<?php

namespace Crumbls\Subscriptions\Tests\Fixtures;

use Crumbls\Subscriptions\Traits\HasPlanSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPlanSubscriptions;

    protected $fillable = ['name', 'email'];
}
