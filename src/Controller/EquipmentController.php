<?php

namespace App\Controller;

use App\Entity\Equipment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/equipments')]
class EquipmentController extends AbstractController
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

    //Ajout d'un équipement
    #[Route('/api/equipments', name: 'equipment_create', methods: ['POST'])]
    public function addEquipment(Request $request): JsonResponse
    {
        $json = $request->getContent();

        if (!is_string($json)) {
            return new JsonResponse(
                ['message' => 'Invalid JSON data'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($json, true);

        if ($data === null) {
            return new JsonResponse(
                ['message' => 'Invalid JSON data'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

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

    //Modification d'un équipement
    #[Route('/api/equipments/{id}', name: 'equipment_update', methods: ['PUT'])]
    public function updateEquipment(Request $request, int $id): JsonResponse
    {
        $equipment = $this->entityManager->getRepository(Equipment::class)->find($id);

        if ($equipment === null) {
            return new JsonResponse(
                ['message' => 'Equipment not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $json = $request->getContent();

        if (!is_string($json)) {
            return new JsonResponse(
                ['message' => 'Invalid JSON data'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($json, true);

        if ($data === null) {
            return new JsonResponse(
                ['message' => 'Invalid JSON data'],
                JsonResponse::HTTP_BAD_REQUEST
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

    //Suppression d'un équipement
    #[Route('/api/equipments/{id}', name: 'equipment_delete', methods: ['DELETE'])]
    public function deleteEquipment(int $id): JsonResponse
    {
        $equipment = $this->entityManager->getRepository(Equipment::class)->find($id);

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
                ['message' => 'Equipment deleted successfully'],
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

    //Affichage de tous les équipements non supprimes
    #[Route('/api/equipments', name: 'equipment_list', methods: ['GET'])]
    public function getEquipments(Request $request): JsonResponse
    {
        try {
            $equipmentRepository = $this->entityManager->getRepository(Equipment::class);
            $category = $request->query->get('category');
            $criteria = ['deletedAt' => null];

            if ($category) {
                $criteria['category'] = $category;
            }

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
