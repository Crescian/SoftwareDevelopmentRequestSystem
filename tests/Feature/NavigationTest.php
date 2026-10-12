<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mst_user_sees_workflow_queue_links_on_dashboard(): void
    {
        $mst = User::factory()->mst()->create();

        $response = $this->actingAs($mst)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Initial Review');
        $response->assertSee('Feasibility Assessment');
        $response->assertSee(route('workflow.monitoring.index'), false);
    }

    public function test_non_mst_user_does_not_see_workflow_queue_links(): void
    {
        $requestor = User::factory()->create();

        $response = $this->actingAs($requestor)->get(route('software-projects.index'));

        $response->assertOk();
        $response->assertDontSee('Initial Review');
        $response->assertDontSee(route('workflow.monitoring.index'), false);
    }

    public function test_my_approvals_sidebar_link_is_gone(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('software-projects.index'))
            ->assertDontSee(route('approvals.management.index'), false);
    }

    public function test_requestor_does_not_see_a_dashboard_link(): void
    {
        $requestor = User::factory()->create();

        $this->actingAs($requestor)
            ->get(route('software-projects.index'))
            ->assertDontSee('Dashboard');
    }
}
