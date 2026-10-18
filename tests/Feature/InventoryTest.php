<?php

namespace Tests\Feature;

use Tests\TestCase;

class InventoryTest extends TestCase
{
    /**
     * Test inventory page can be rendered successfully.
     */
    public function test_inventory_page_can_be_rendered(): void
    {
        $response = $this->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee('Inventaris Perpajakan & Aset');
        $response->assertViewIs('inventory.index');
    }

    /**
     * Test homepage application modal contains the inventory link.
     */
    public function test_homepage_modal_contains_inventory_link(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(200);
        $response->assertSee(route('inventory.index'));
    }
}
