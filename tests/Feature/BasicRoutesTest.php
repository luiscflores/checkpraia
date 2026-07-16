<?php

test('homepage is accessible', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

test('login page is accessible', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('admin dashboard redirects when not logged in', function () {
    $response = $this->get('/en/admin-dashboard');
    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('admin dashboard is accessible when logged in as admin', function () {
    $admin = \App\Models\User::factory()->create(['is_admin' => true]);
    $response = $this->actingAs($admin)->get('/en/admin-dashboard');
    $response->assertStatus(200);
});
