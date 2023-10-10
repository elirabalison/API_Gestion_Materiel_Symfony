<?php

namespace App\Controller;

use App\Service\EquipmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/equipments')]
class EquipmentController extends AbstractController
{
    private EquipmentService $equipmentService;

    public function __construct(EquipmentService $equipmentService)
    {
        $this->equipmentService = $equipmentService;
    }

    //Ajout d'un équipement
    #[Route('', name: 'equipment_create', methods: ['POST'])]
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
        return $this->equipmentService->createEquipment($data);
    }

    //Modification d'un équipement
    #[Route('/{id}', name: 'equipment_update', methods: ['PUT'])]
    public function updateEquipment(Request $request, int $id): JsonResponse
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
        return $this->equipmentService->updateEquipment($data, $id);
    }

    //Suppression d'un équipement
    #[Route('/{id}', name: 'equipment_delete', methods: ['DELETE'])]
    public function deleteEquipment(int $id): JsonResponse
    {
        return $this->equipmentService->deleteEquipment($id);
    }

    //Affichage de tous les équipements non supprimes
    #[Route('', name: 'equipment_list', methods: ['GET'])]
    public function getEquipments(Request $request): JsonResponse
    {
        $filters = [];
        $filters['id'] = $request->query->get('id');
        $filters['name'] = $request->query->get('name');
        $filters['category'] = $request->query->get('category');

        return $this->equipmentService->getEquipments($filters);
    }
}
