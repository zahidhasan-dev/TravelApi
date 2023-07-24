<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Tour;
use App\Models\User;
use App\Models\Travel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;


    public function test_admin_tours_list_returns_with_paginated_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));
        
        $travel = Travel::factory()->create();
        Tour::factory(16)->create([
            'travel_id' => $travel->id
        ]);

        $response = $this->actingAs($user)->get('/api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.current_page', 1);
    }


    public function test_public_user_cannot_access_adding_tour(): void
    {
        $travel = Travel::factory()->create();
        
        $response = $this->postJson('api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(401);
    }


    public function test_tour_added_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson('api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'something',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'something']);
    }


    public function test_tour_returns_validation_errors_with_invalid_data(): void 
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travel = Travel::factory()->create();

        $tour = $travel->tours()->create([
            'name' => 'something',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);

        $response = $this->actingAs($user)->putJson('api/v1/admin/travels/'.$travel->id.'/tours/'.$tour->id, [
            'name' => 'something updated',
            'starting_date' => '',
            'ending_date' => 'abc',
            'price' => 'abc',
        ]);
        $response->assertStatus(422);
    }


    public function test_tour_updated_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travelOne = Travel::factory()->create();
        $travelTwo = Travel::factory()->create();

        $tourOne = $travelOne->tours()->create([
            'name' => 'tour one',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);

        $tourTwo = $travelTwo->tours()->create([
            'name' => 'tour two',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);

        $response = $this->actingAs($user)->putJson('api/v1/admin/travels/'.$travelOne->id.'/tours/'.$tourTwo->id, [
            'name' => 'tour two updated',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);
        $response->assertStatus(404);

        $response = $this->actingAs($user)->putJson('api/v1/admin/travels/'.$travelOne->id.'/tours/'.$tourOne->id, [
            'name' => 'tour one updated',
            'starting_date' => '2023-07-24',
            'ending_date' => '2023-07-25',
            'price' => 456
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'tour one updated']);
    }
}
