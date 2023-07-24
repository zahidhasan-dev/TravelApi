<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{
    use RefreshDatabase;


    public function test_admin_travels_list_returns_all_data_with_pagination():void 
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $publicTravel = Travel::factory()->create(['is_public' => true]);
        $nonPublicTravel = Travel::factory()->create(['is_public' => false]);
        Travel::factory(15)->create();

        $response = $this->actingAs($user)->get('api/v1/admin/travels');
        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonPath('data.0.id', $publicTravel->id);
        $response->assertJsonPath('data.1.id', $nonPublicTravel->id);
    }


    public function test_public_user_cannot_access_adding_travel(): void
    {   
        $response = $this->postJson('api/v1/admin/travels');
        $response->assertStatus(401);
    }


    public function test_non_admin_user_cannot_access_adding_travel(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $response = $this->actingAs($user)->postJson('api/v1/admin/travels');
        $response->assertStatus(403);
    }


    public function test_travel_added_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('api/v1/admin/travels', [
            'name' => 'travel name',
        ]);
        $response->assertStatus(422);

        $response = $this->actingAs($user)->postJson('api/v1/admin/travels', [
            'name' => 'travel name',
            'is_public' => 1,
            'description' => 'something',
            'number_of_days' => 7,
        ]);
        $response->assertStatus(201);
        
        $response = $this->get('api/v1/travels');
        $response->assertJsonFragment(['name' => 'travel name']);
    }


    public function test_public_user_cannot_access_updating_travel():void 
    {
        $response = $this->putJson('api/v1/admin/travels/1');
        $response->assertStatus(401);
    }


    public function test_travel_update_returns_validation_errors_with_invalid_data(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travelOne = Travel::factory()->create([
            'name' => 'travel one',
            'description' => 'some description one',
            'number_of_days' => 3,
        ]);

        $travelTwo = Travel::factory()->create([
            'name' => 'travel two',
            'description' => 'some description two',
            'number_of_days' => 7,
        ]);

        $response = $this->actingAs($user)->putJson('api/v1/admin/travels/'.$travelOne->id, [
            'name' => 'travel two',
            'description' => 'some description one',
            'number_of_days' => 3,
        ]);
        $response->assertStatus(422);
    }


    public function test_travel_updated_successfully_with_valid_data():void 
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travel = Travel::factory()->create([
            'name' => 'something',
            'description' => 'some description',
            'number_of_days' => 7,
        ]);

        $response = $this->actingAs($user)->putJson('api/v1/admin/travels/'.$travel->id, [
            'name' => 'something updated',
            'description' => 'some description',
            'number_of_days' => 6,
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $travel->id]);
        $response->assertJsonFragment(['name' => 'something updated']);

    }
    
}
