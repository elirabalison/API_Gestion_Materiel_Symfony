<?php

namespace App\Tests\Controller;

use App\Service\EquipmentService;
use App\Controller\EquipmentController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class EquipmentControllerUnitTest extends TestCase
{
    /** @var EquipmentService|\PHPUnit\Framework\MockObject\MockObject */
    private $equipmentService;

    /** @var EquipmentController */
    private $controller;

    public function testAddEquipment(): void
    {
        $equipmentData = [
            'name' => 'iPhone X 128GB',
            'category' => 'Téléphone',
            'number' => 1234567890,
            'description' => 'Cet iPhone est en parfait état, avec une batterie fiable'
        ];

        $this->equipmentService->method('createEquipment')
            ->willReturn(new JsonResponse($equipmentData, JsonResponse::HTTP_CREATED));

        $request = $this->createJsonRequest('POST', '/api/equipments', $equipmentData);
        $response = $this->controller->addEquipment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
    public function testUpdateEquipment(): void
    {
        $existingEquipmentId = 1;
        $equipmentData = [
            'name' => 'Nouveau Nom',
            'category' => 'Nouvelle Catégorie',
            'number' => 'Nouveau Numéro',
            'description' => 'Nouvelle Description'
        ];

        $this->equipmentService->method('updateEquipment')
            ->willReturn(new JsonResponse($equipmentData, JsonResponse::HTTP_OK));

        $request = $this->createJsonRequest('PUT', "/api/equipments/{$existingEquipmentId}", $equipmentData);

        $response = $this->controller->updateEquipment($request, $existingEquipmentId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testDeleteEquipment(): void
    {
        $existingEquipmentId = 1;

        $response = new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);

        $this->equipmentService->method('deleteEquipment')
            ->willReturn($response);

        $actualResponse = $this->controller->deleteEquipment($existingEquipmentId);
        $this->assertInstanceOf(JsonResponse::class, $actualResponse);
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $actualResponse->getStatusCode());
    }

    public function testGetEquipments(): void
    {
        $equipmentData = [
            ['id' => 1, 'name' => 'iPhone X 128Go', 'category' => 'Téléphone'],
            ['id' => 2, 'name' => 'MacBook Air 13', 'category' => 'Ordinateur']
        ];

        $this->equipmentService->method('getEquipments')
            ->willReturn(new JsonResponse($equipmentData, JsonResponse::HTTP_OK));

        $request = Request::create('/api/equipments', 'GET', [
            'id' => 1,
            'name' => 'Equipment 1',
            'category' => 'Category A'
        ]);

        $response = $this->controller->getEquipments($request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($equipmentData, $responseData);
    }

    protected function setUp(): void
    {
        $this->equipmentService = $this->createMock(EquipmentService::class);
        $this->controller = new EquipmentController($this->equipmentService);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<string, mixed> $data
     */
    private function createJsonRequest(string $method, string $uri, array $data): Request
    {
        return Request::create($uri, $method, [], [], [], [], json_encode($data));
    }
}
