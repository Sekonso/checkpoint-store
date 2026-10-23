<?php

namespace Tests\Support;

use App\Models\User;

final class UserFixtures
{
    /**
     * Create an admin user for authenticated request tests.
     */
    public static function admin(): User
    {
        return User::factory()->asAdmin()->create();
    }
}
