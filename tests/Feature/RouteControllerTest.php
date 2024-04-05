<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Route;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

// use PHPUnit\Framework\TestCase;

class RouteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $user;
    protected $route;

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->token) {
            $this->token = $this->createTestUserAndGetToken();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createTestUserAndGetToken()
    {
        $user = User::factory()->create();
        $this->user = $user;
        return auth()->login($user);
    }

    /**
     * Test index method.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get('/api/routes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'routes' => [],
                'status',
            ]);
    }

    // /**
    //  * Test store method.
    //  *
    //  * @return void
    //  */
    // public function testStore()
    // {
    //     // Create a category
    //     $category = Category::factory()->create();

    //     // Mock the file upload
    //     Storage::fake('public'); // Assuming you're using public disk for file storage
    //     $image = UploadedFile::fake()->image('test_image.jpg');

    //     // Make a POST request to your API route
    //     $response = $this->postJson('/api/routes/create', [
    //         'title' => 'Test Route',
    //         'category_id' => $category->id,
    //         'duration' => 120,
    //         'picture' => $image,
    //         'destinations' => '1,2',
    //         'user_id' => $this->user->id
    //     ]);

    //     // Assert the response
    //     $response->assertStatus(201)
    //     ->assertJson([
    //         'message' => 'Route created successfully',
    //         'status' => 201,
    //     ]);

    //     // Optionally, you can assert that the file was stored correctly
    //     Storage::disk('public')->assertExists('path/to/expected/image.jpg');

    //     // Retrieve and store route data for further testing, if needed
    //     $routeData = $response->json()['route'];
    //     $this->route = $routeData;
    // }
    // /**
    //  * Test update method.
    //  * @depends testStore
    //  * @return void
    //  */
    // public function testUpdate()
    // {
    //     $route = $this->route;
    //     $category = Category::factory()->create();
    //     $image = UploadedFile::fake()->image('test_image.jpg');

    //     $routeId = $route['id'];

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $this->token,
    //     ])->putJson("/api/routes/{$routeId}/update", [
    //         'title' => 'Updated Test Route',
    //         'category_id' => $category->id,
    //         'duration' => 180,
    //         'picture' => $image,
    //         'destinations' => '1,2',
    //         'user_id' => $this->user->id

    //     ]);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'message' => 'Route updated successfully',
    //             'status' => 200,
    //         ]);
    // }

    // /**
    //  * Test destroy method.
    //  * @depends testStore
    //  * @return void
    //  */
    // public function testDestroy()
    // {
    //     $route = $this->route;
    //     $routeId = $route['id'];

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $this->token,
    //     ])->delete("/api/routes/{$routeId}/delete");

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'message' => 'Route deleted successfully',
    //             'status' => 200,
    //         ]);
    // }

    // /**
    //  * Test addToWatchlist method.
    //  * @return void
    //  */
    // public function testAddToWatchlist()
    // {
    //     $route = Route::factory()->create();

    //     $response = $this->post("/api/addToWatchlist/{$route->id}", [], [
    //         'Authorization' => 'Bearer ' . $this->token,
    //     ]);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'message' => 'Route added to the watchlist successfully',
    //             'status' => 200,
    //         ]);
    // }

    /**
     * Test search method.
     *
     * @return void
     */
    public function testSearch()
    {
        $response = $this->postJson('/api/routes/search', [
            'name' => 'beach',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Search By Category',
                'status' => 200,
            ]);
    }

    /**
     * Test createDestination method.
     *
     * @return void
     */
    public function testCreateDestination()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/destinations/create', [
            'name' => 'Test Destination',
            'location' => 'Test Location',
            'recommendations' => 'Test Recommendation 1, Test Recommendation 2',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Destination created successfully',
                'status' => 201,
            ]);
    }

    /**
     * Test register method.
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'user',
                'authorisation' => [
                    'token',
                    'type',
                ],
            ]);
    }

    /**
     * Test logout method.
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
    }
}
