<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// REGISTRATION TESTS
// ========================================

test('user can register with valid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Registration successful!',
        ]);

    // Verify user was created in database
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    // Verify token was created
    $user = User::where('email', 'john@example.com')->first();
    expect($user->tokens)->toHaveCount(1);
});

test('registration fails with duplicate email', function () {
    // Create existing user
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration fails without required fields', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('registration fails with short password', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '1234567', // Only 7 characters
        'password_confirmation' => '1234567',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration fails when passwords do not match', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration fails with invalid email format', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'notanemail',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

// ========================================
// LOGIN TESTS
// ========================================

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Login successful!',
        ]);

    // Verify token was created
    expect($user->fresh()->tokens)->toHaveCount(1);
});

test('login fails with incorrect password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login fails with non-existent email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login fails without required fields', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('user can login multiple times and get different tokens', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // First login
    $response1 = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // Second login
    $response2 = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $token1 = $response1->json('token');
    $token2 = $response2->json('token');

    // Tokens should be different
    expect($token1)->not->toBe($token2);

    // User should have 2 tokens
    expect($user->fresh()->tokens)->toHaveCount(2);
});

// ========================================
// LOGOUT TESTS
// ========================================

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout successful!',
        ]);

    // Verify token was deleted
    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('logout fails without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

test('logout only revokes current token', function () {
    $user = User::factory()->create();

    // Create 3 tokens
    $token1 = $user->createToken('device1');
    $token2 = $user->createToken('device2');
    $token3 = $user->createToken('device3');

    $this->assertCount(3, $user->tokens);

    // Act as user with specific token and logout
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/logout');

    $response->assertOk();

    // Refresh user and check token count
    $user->refresh();
    $this->assertCount(2, $user->tokens);
});

test('cannot use token after logout', function () {
    $user = User::factory()->create();

    // Authenticate and get response
    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password', // Default factory password
    ]);

    $token = $loginResponse->json('token');

    // Verify authenticated access works
    $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/user')
        ->assertOk();

    // Logout
    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/logout')
        ->assertOk();

    // Try to access again with same token
    $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/user')
        ->assertUnauthorized();
});




test('unauthenticated user cannot access user endpoint', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});
