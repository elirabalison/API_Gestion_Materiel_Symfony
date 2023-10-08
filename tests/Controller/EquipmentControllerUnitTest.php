<?php

namespace App\Tests\Controller;

use App\Entity\Equipment;
use App\Controller\EquipmentController;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class EquipmentControllerUnitTest extends TestCase
{
    private $entityManager;
    private $validator;
    private $controller;
    private $equipmentRepository;


    public function testAddEquipment(): void
    {
        $requestData = [
            'name' => 'iPhone X 128GB',
            'category' => 'Téléphone',
            'number' => 1234567890,
            'description' => 'Cet iPhone est en parfait état, avec une batterie fiable'
        ];

        $request = $this->createJsonRequest('POST', '/api/equipments', $requestData);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Equipment::class));
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->controller->addEquipment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(['message' => 'Equipment added successfully'], $responseData);
    }
    public function testUpdateEquipment(): void
    {
        $existingEquipmentId = 1;

        $this->configureEntityManagerToFindEquipment($existingEquipmentId);

        $existingEquipment = $this->entityManager->getRepository(Equipment::class)->find($existingEquipmentId);
        $this->assertNotNull($existingEquipment);

        $requestData = [
            'name' => 'Nouveau Nom',
            'category' => 'Nouvelle Catégorie',
            'number' => 'Nouveau Numéro',
            'description' => 'Nouvelle Description'
        ];

        $request = $this->createJsonRequest('PUT', "/api/equipments/{$existingEquipmentId}", $requestData);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->controller->updateEquipment($request, $existingEquipmentId);

        $expectedResponse = new JsonResponse(
            ['message' => 'Equipment updated successfully'],
            JsonResponse::HTTP_OK
        );
        $this->assertEquals($expectedResponse->getContent(), $response->getContent());
    }


    public function testDeleteEquipment(): void
    {
        $existingEquipmentId = 1;

        $this->configureEntityManagerToFindEquipment($existingEquipmentId);

        $existingEquipment = $this->entityManager->getRepository(Equipment::class)->find($existingEquipmentId);
        $this->assertNotNull($existingEquipment);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->controller->deleteEquipment($existingEquipmentId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(['message' => 'Equipment deleted successfully'], $responseData);
    }

    public function testGetEquipments(): void
    {
        $request = new Request([], ['category' => 'Téléphone']);

        $equipments = [
           new Equipment(),
           new Equipment(),
        ];

        $this->equipmentRepository->expects($this->once())
                           ->method('findBy')
                           ->willReturn($equipments);

        $this->entityManager->expects($this->once())
                      ->method('getRepository')
                      ->willReturn($this->equipmentRepository);

        $response = $this->controller->getEquipments($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(2, $responseData);
    }


    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->equipmentRepository = $this->createMock(EquipmentRepository::class);
        $this->controller = new EquipmentController($this->entityManager, $this->validator);
    }

    private function createJsonRequest($method, $uri, $data): Request
    {
        return Request::create($uri, $method, [], [], [], [], json_encode($data));
    }

    private function configureEntityManagerToFindEquipment($existingEquipmentId): void
    {
        $this->equipmentRepository->method('find')
                ->willReturnCallback(function ($id) use ($existingEquipmentId): ?Equipment {
                    if ($id === $existingEquipmentId) {
                        $equipment = new Equipment();
                        $equipment->setId($existingEquipmentId);
                        return $equipment;
                    }
                    return null;
                });

            $this->entityManager->method('getRepository')
                ->willReturn($this->equipmentRepository);
    }
}
