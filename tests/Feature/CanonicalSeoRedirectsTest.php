<?php

namespace Tests\Feature;

use Tests\TestCase;

class CanonicalSeoRedirectsTest extends TestCase
{
    public function test_public_prefixed_urls_redirect_to_clean_urls(): void
    {
        $this->get('https://naiprints.co.ke/public/shop?category=men')
            ->assertMovedPermanently()
            ->assertRedirect('https://naiprints.co.ke/shop?category=men');
    }

    public function test_www_urls_redirect_to_non_www_host(): void
    {
        $this->get('https://www.naiprints.co.ke/shop')
            ->assertMovedPermanently()
            ->assertRedirect('https://naiprints.co.ke/shop');
    }
}
