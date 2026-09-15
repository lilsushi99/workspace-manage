<?php

namespace Tests\Unit;

use App\Rules\ReservedSlug;
use Tests\TestCase;

class ReservedSlugRuleTest extends TestCase
{
    public function test_reserved_slugs_fail_validation()
    {
        $rule = new ReservedSlug();
        $failed = false;

        $rule->validate('slug', 'admin', function ($message) use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_valid_store_slug_passes_validation()
    {
        $rule = new ReservedSlug();
        $failed = false;

        $rule->validate('slug', 'my-awesome-smm-store', function ($message) use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }
}
