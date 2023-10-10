<?php

namespace App\Service;

use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class EquipmentService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createEquipment(array $data): JsonResponse
    {
        $equipment = new Equipment();
        $equipment->setName($data['name']);
        $equipment->setCategory($data['category']);
        $equipment->setNumber($data['number']);
        $equipment->setDescription($data['description'] ?? '');
        $equipment->setCreatedAt(new \DateTime());

        $errors = $this->validator->validate($equipment);

        if ($errors !== null && count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(
                ['errors' => $errorMessages],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($equipment);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(
                ['message' => 'Equipment added successfully'],
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return new JsonResponse(
                ['message' => 'An error occurred while adding the equipment'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateEquipment(array $data, int $id): JsonResponse
    {
        $equipment = $this->entityManager->find(Equipment::class, $id);

        if ($equipment === null) {
            return new JsonResponse(
                ['message' => 'Equipment not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $equipment->setName($data['name']);
        $equipment->setCategory($data['category']);
        $equipment->setNumber($data['number']);
        $equipment->setDescription($data['description'] ?? '');
        $equipment->setUpdatedAt(new \DateTime());

        $errors = $this->validator->validate($equipment);

        if ($errors !== null && count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(
                ['errors' => $errorMessages],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(
                ['message' => 'Equipment updated successfully'],
                JsonResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return new JsonResponse(
                ['message' => 'An error occurred while updating the equipment'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function deleteEquipment(int $id): JsonResponse
    {
        $equipment = $this->entityManager->find(Equipment::class, $id);

        if ($equipment === null) {
            return new JsonResponse(
                ['message' => 'Equipment not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $equipment->setDeletedAt(new \DateTime());

        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse(
                '',
                JsonResponse::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return new JsonResponse(
                ['message' => 'An error occurred while deleting the equipment'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getEquipments(array $filters = []): JsonResponse
    {
        try {
            $criteria = ['deletedAt' => null];

            if (isset($filters['id'])) {
                $criteria['id'] = $filters['id'];
            }

            if (isset($filters['name'])) {
                $criteria['name'] = $filters['name'];
            }

            if (isset($filters['category'])) {
                $criteria['category'] = $filters['category'];
            }

            $equipmentRepository = $this->entityManager->getRepository(Equipment::class);
            $equipments = $equipmentRepository->findBy($criteria);

            if ($equipments === []) {
                return new JsonResponse(
                    ['message' => 'Equipment not found'],
                    JsonResponse::HTTP_NOT_FOUND
                );
            }

            $response = [];
            foreach ($equipments as $equipment) {
                $response[] = [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                    'category' => $equipment->getCategory(),
                    'number' => $equipment->getNumber(),
                    'description' => $equipment->getDescription(),
                    'createdAt' => $equipment->getCreatedAt()
                        ? $equipment->getCreatedAt()->format('Y-m-d H:i:s') : null,
                    'updatedAt' => $equipment->getUpdatedAt()
                        ? $equipment->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                ];
            }

            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['message' => 'An error occurred while retrieving the equipments'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
