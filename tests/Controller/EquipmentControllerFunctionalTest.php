<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EquipmentControllerFunctionalTest extends WebTestCase
{
    public function testAddEquipment(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'iPhone X 128GB',
            'category' => 'Téléphone',
            'number' => 1234567890,
            'description' => 'Cet iPhone est en parfait état, avec une batterie fiable.'
        ];

        $client->request('POST', '/api/equipments', [], [], [], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(['message' => 'Equipment added successfully'], $responseData, $response->getContent());
    }

    public function testUpdateEquipment(): void
    {
        $client = static::createClient();

        $data = [
            'name' => '',
            'category' => 'Updated Category',
            'number' => 1234567890,
            'description' => 'Updated Description'
        ];

        $client->request('PUT', '/api/equipments/45', [], [], [], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testDeleteEquipment(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/equipments/43');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }


    public function testGetEquipments(): void
    {
        $pattern = '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/';
        $client = static::createClient();
        $client->request('GET', '/api/equipments?category=Téléphone');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertArrayHasKey('category', $responseData[0]);
        $this->assertArrayHasKey('number', $responseData[0]);
        $this->assertArrayHasKey('description', $responseData[0]);
        $this->assertArrayHasKey('createdAt', $responseData[0]);
        $this->assertArrayHasKey('updatedAt', $responseData[0]);

        $this->assertIsInt($responseData[0]['id']);
        $this->assertIsString($responseData[0]['name']);
        $this->assertIsString($responseData[0]['category']);
        $this->assertIsString($responseData[0]['number']);
        $this->assertIsString($responseData[0]['description']);
        $this->assertMatchesRegularExpression($pattern, $responseData[0]['createdAt']);
        if ($responseData[0]['updatedAt'] !== null) {
            $this->assertMatchesRegularExpression($pattern, $responseData[0]['updatedAt']);
        }
    }
}
