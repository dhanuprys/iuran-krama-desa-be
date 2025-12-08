<?php

namespace Tests\Unit;

use App\Helpers\ResponseHelper;
use PHPUnit\Framework\TestCase;

class ResponseHelperTest extends TestCase
{
    public function test_success_response_structure()
    {
        $data = ['key' => 'value'];
        $response = ResponseHelper::success($data);

        $this->assertTrue($response['success']);
        $this->assertNull($response['error']);
        $this->assertEquals($data, $response['data']);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('trace_id', $response['meta']);
        $this->assertArrayHasKey('timestamp', $response['meta']);
    }

    public function test_error_response_structure()
    {
        $code = 'ERR-TEST-001';
        $message = 'Test error message';
        $details = ['field' => 'error details'];

        $response = ResponseHelper::error($code, $message, $details);

        $this->assertFalse($response['success']);
        $this->assertEquals($code, $response['error']['code']);
        $this->assertEquals($message, $response['error']['message']);
        $this->assertEquals($details, $response['error']['details']);
        $this->assertArrayHasKey('meta', $response);
    }

    public function test_paginated_response_structure()
    {
        $data = [['id' => 1]];
        $pagination = ['total' => 1, 'per_page' => 15];

        $response = ResponseHelper::paginated($data, $pagination);

        $this->assertTrue($response['success']);
        $this->assertEquals($data, $response['data']);
        $this->assertEquals($pagination, $response['pagination']);
        $this->assertArrayHasKey('meta', $response);
    }
}
