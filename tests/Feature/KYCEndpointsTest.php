<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\KYC\app\Services\YouverifyService;
use Mockery;
use Tests\TestCase;

class KYCEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_can_initiate_kyc_verification(): void
    {
        $user = User::factory()->create();

        $service = Mockery::mock(YouverifyService::class);
        $service->shouldReceive('startIdentityVerification')
            ->once()
            ->andReturn(['data' => ['reference' => 'ref-123']]);

        $this->app->instance(YouverifyService::class, $service);

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'country' => 'NG',
            'document_type' => 'passport',
            'document_number' => 'A1234567',
            'email' => 'jane@example.com',
        ];

        $this->actingAs($user, 'api')
            ->postJson('/api/kyc/verify', $payload)
            ->assertCreated()
            ->assertJsonFragment(['reference' => 'ref-123']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $service = Mockery::mock(YouverifyService::class);
        $service->shouldReceive('validateWebhookSignature')->once()->andReturn(false);

        $this->app->instance(YouverifyService::class, $service);

        $this->postJson('/api/kyc/webhook', ['reference' => 'abc'])
            ->assertStatus(403);
    }
}

