<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscriptionTrajet', name: 'app_inscription', methods: "POST")]
    public function listeInscription(): JsonResponse
    {
        // TODO : Récupérer la liste des inscriptions (table : passenger + )
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InscriptionController.php',
        ]);
    }

    #[Route('/listeInscriptionConducteur/{idtrajet}', name: 'app_list_inscription_conducteur', methods: "GET")]
    public function listeInscriptionConducteur($idtrajet): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InscriptionController.php',
        ]);
    }
    #[Route('/listeInscriptionUser/{idpers}', name: 'app_list_inscription_user', methods: "GET")]
    public function listeInscriptionUser($idpers): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InscriptionController.php',
        ]);
    }
    #[Route('/insertInscription/{idpers}/{idtrajet}', name: 'app_insert_inscription', methods: "POST")]
    public function insertInscription($idpers, $idtrajet): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InscriptionController.php',
        ]);
    }
    #[Route('/deleteInscription/{id}', name: 'app_delete_inscription', methods: "DELETE")]
    public function deleteInscription($id): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InscriptionController.php',
        ]);
    }
}
