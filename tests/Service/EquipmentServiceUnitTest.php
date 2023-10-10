<?php

namespace App\Tests\Service;

use App\Entity\Equipment;
use App\Service\EquipmentService;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class EquipmentServiceUnitTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $validator;

    /** @var EquipmentService */
    private $service;

    public function testAddEquipment(): void
    {
        $data = [
            'name' => 'iPhone X 128GB',
            'category' => 'Téléphone',
            'number' => 1234567890,
            'description' => 'Cet iPhone est en parfait état, avec une batterie fiable'
        ];

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Equipment::class));
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->service->createEquipment($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(['message' => 'Equipment added successfully'], $responseData);
    }
    public function testUpdateEquipment(): void
    {
        $existingEquipmentId = 1;

        $existingEquipment = new Equipment();

        $this->entityManager->expects($this->once())
            ->method('find')
            ->with($this->equalTo(Equipment::class), $this->equalTo($existingEquipmentId))
            ->willReturn($existingEquipment);

        $data = [
            'name' => 'Nouveau Nom',
            'category' => 'Nouvelle Catégorie',
            'number' => 'Nouveau Numéro',
            'description' => 'Nouvelle Description'
        ];

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->service->updateEquipment($data, $existingEquipmentId);

        $expectedResponse = new JsonResponse(
            ['message' => 'Equipment updated successfully'],
            JsonResponse::HTTP_OK
        );
        $this->assertEquals($expectedResponse->getContent(), $response->getContent());
    }


    public function testDeleteEquipment(): void
    {
        $existingEquipmentId = 1;

        $existingEquipment = new Equipment();

        $this->entityManager->expects($this->once())
            ->method('find')
            ->with($this->equalTo(Equipment::class), $this->equalTo($existingEquipmentId))
            ->willReturn($existingEquipment);

        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->entityManager->expects($this->once())
            ->method('commit');

        $response = $this->service->deleteEquipment($existingEquipmentId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('', $responseData);
    }

    public function testGetEquipments(): void
    {
        $filter = ['deletedAt' => null];

        $equipmentRepository = $this->createMock(EquipmentRepository::class);

        $equipmentEntity1 = new Equipment();
        $equipmentEntity1->setId(1);

        $equipmentEntity2 = new Equipment();
        $equipmentEntity2->setId(2);

        $equipmentRepository->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo(['deletedAt' => null]))
            ->willReturn([$equipmentEntity1, $equipmentEntity2]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Equipment::class)
            ->willReturn($equipmentRepository);

        $response = $this->service->getEquipments($filter);
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
        $this->service = new EquipmentService($this->entityManager, $this->validator);
    }
}
