<?php

namespace Tests\Feature;

use App\Models\ContentItem;
use App\Models\ContentSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $heroSection = ContentSection::create([
            'key' => 'hero',
            'title' => 'Build your own social media reseller business.',
            'description' => 'Dynamic CMS hero description from database.',
            'status' => 'active',
        ]);

        ContentItem::create([
            'section_id' => $heroSection->id,
            'title' => 'Jane Reseller',
            'description' => 'Top Reseller',
            'status' => 'active',
            'sort_order' => 1,
        ]);
    }

    public function test_landing_page_loads_and_renders_cms_content()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Build your own social media reseller business.');
        $response->assertSee('Dynamic CMS hero description from database.');
        $response->assertSee('Jane Reseller');
    }

    public function test_marketing_subpages_load_successfully()
    {
        $this->get('/features')->assertStatus(200);
        $this->get('/pricing')->assertStatus(200);
        $this->get('/resources')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
    }

    public function test_updating_cms_database_item_updates_rendered_landing_page()
    {
        $heroItem = ContentItem::first();
        $heroItem->update(['title' => 'Updated Reseller Founder']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Updated Reseller Founder');
    }

    public function test_public_pages_contain_registration_and_login_ctas()
    {
        $response = $this->get('/');
        $response->assertSee(route('register'));
        $response->assertSee(route('login'));
    }
}
