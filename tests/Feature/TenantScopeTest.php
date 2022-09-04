<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function a_model_has_a_tenant_id_on_the_migration()
    {
        $now = now();
        $this->artisan('make:model Test -m');

        // find the migration file and check it has a tenant_id on it
        $filename = $now->year.'_'.$now->format('m').'_'.$now->format('d').'_'.$now->format('h')
            .$now->format('i').$now->format('s').
            '_create_tests_table.php';
        $this->assertTrue(File::exists(database_path('migrations/'.$filename)));
        $this->assertStringContainsString(
            '$table->unsignedBigInteger(\'tenant_id\')->index();',
            File::get(database_path('migrations/'.$filename))
        );
        // clean up
        File::delete(database_path('migrations/'.$filename));
        File::delete(app_path('Test.php'));
    }

    /** @test */
    public function a_user_can_only_see_users_in_the_same_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1,
        ]);

        User::factory(9)->create([
            'tenant_id' => $tenant1,
        ]);

        User::factory(10)->create([
            'tenant_id' => $tenant2,
        ]);

        // auth()->login($user1);
        $this->actingAs($user1);

        $this->assertEquals(10, User::count());
    }

    /** @test */
    public function test_a_user_can_only_create_a_user_in_his_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1,
        ]);

        // auth()->login($user1);
        $this->actingAs($user1);

        $createdUser = User::factory()->create();

        $this->assertTrue($createdUser->tenant_id == $user1->tenant_id);
    }

    /** @test */
    public function test_a_user_can_only_create_a_user_in_his_tenant_even_if_other_tenant_is_provided()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1,
        ]);

        auth()->login($user1);

        $createdUser = User::factory()->make();
        $createdUser->tenant_id = $tenant2->id;
        $createdUser->save();

        $this->assertTrue($createdUser->tenant_id == $user1->tenant_id);
    }
}
