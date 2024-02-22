<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Marque;
use App\Utils\Validation;
use App\Controller\ValidationException;

class MarqueController extends AbstractController
{
    #[Route('/listeMarque', name: 'app_liste_marque', methods: "GET")]
    public function listeMarque(EntityManagerInterface $em): JsonResponse
    {
        $marques = $em->getRepository(Marque::class)->findAll();
        $marquesJson = [];
        foreach ($marques as $marque) {
            $marquesJson['id'] = $marque->getId();
            $marquesJson['id']['nom'] = $marque->getNom();
            // TODO : récupération de la liste des voitures
        }
        return $this->json($marquesJson, 200);
    }

    #[Route('/insertMarque/{nom}', name: 'app_insert_marque', methods: "POST")]
    public function insertMarque($nom, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérification des données
            Validation::validateMarque($nom);

            // Nettoyer les données
            $nomNettoyer = Validation::nettoyage($nom);

            // Créer une nouvelle instance de Marque
            $marque = new Marque();
            $marque->setNom($nomNettoyer);

            // Sauvegarder la marque en base de donnée
            $em->getRepository(Marque::class)->persist($marque);
            $em->flush();

            return $this->json([
                'message' => 'Marque ajoutée avec succès!',
            ], 200);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/deleteMarque/{id}', name: 'app_delete_marque', methods: "DELETE")]
    public function deleteMarque($id, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérification des données
            Validation::validateInt($id);

            // Nettoyer les données
            $idNettoyer = Validation::nettoyage($id);

            // Trouver la marque par son id
            $marque = $em->getRepository(Marque::class)->find($idNettoyer);

            // Check if the Marque entity exists
            if (!$marque) {
                throw new ValidationException('Marque non trouvée');
            }

            // Supprimer la marque de la base de donnée
            $em->remove($marque);
            $em->flush();

            return $this->json([
                'message' => 'Marque supprimée avec succès!',
            ], 200);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
