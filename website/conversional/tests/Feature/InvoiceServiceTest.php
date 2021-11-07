<?php

namespace Tests\Feature;

use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function test_post_api_response()
    {
        $response = $this->post('/api/invoices', [
            'end'         => '2023-11-19',
            'start'       => '2011-11-19',
            'customer_id' => '1',
        ]);

        $response->assertStatus(200);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(isset($content['invoiceId']));
    }

    public function test_get_api_response()
    {
        $response = $this->get('/api/invoices/1');

        $response->assertStatus(200);
        $content = json_decode($response->getContent(), true);

        $this->assertTrue(isset($content['invoiceStatus']));
        $this->assertTrue(isset($content['totalPrice']));
        $this->assertTrue(isset($content['users']));
        $this->assertTrue(isset($content['invoices']));
    }
}
