<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function metrics_endpoint_returns_401_without_token(): void
    {
        config(['metrics.token' => 'secret']);

        $this->get('/metrics')->assertUnauthorized();
    }

    #[Test]
    public function metrics_endpoint_returns_401_with_wrong_token(): void
    {
        config(['metrics.token' => 'secret']);

        $this->get('/metrics', ['Authorization' => 'Bearer wrong'])->assertUnauthorized();
    }

    #[Test]
    public function metrics_endpoint_returns_200_with_correct_token(): void
    {
        config(['metrics.token' => 'secret']);

        $response = $this->get('/metrics', ['Authorization' => 'Bearer secret']);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }

    #[Test]
    public function metrics_endpoint_is_open_when_no_token_configured(): void
    {
        config(['metrics.token' => null]);

        $this->get('/metrics')->assertOk();
    }

    #[Test]
    public function metrics_endpoint_returns_401_when_token_is_empty_string(): void
    {
        config(['metrics.token' => '']);

        $this->get('/metrics')->assertUnauthorized();
    }
}
