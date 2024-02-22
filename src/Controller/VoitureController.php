<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Voiture;
use App\Utils\Validation;
use App\Utils\ValidationException;
use App\Entity\Marque;
use Exception;

class VoitureController extends AbstractController
{
    #[Route('/listeVoiture', name: 'app_liste_voiture', methods: "GET")]
    public function listeVoiture(EntityManagerInterface $em): JsonResponse
    {
        try {
            $repository = $em->getRepository(Voiture::class);
            $voitures = $repository->findAll();

            return $this->json([
                'success' => true,
                'message' => 'Liste des voitures',
                'data' => $voitures,
            ], 200);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des voitures',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    #[Route('/insertVoiture/{modele},{place},{marqueId},{immatriculation}', name: 'app_insert_voiture', methods: "POST")]
    public function insertVoiture(string $modele, int $place, int $marqueId, string $immatriculation, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Validation des données
            Validation::validateString($modele);
            Validation::validateInt($place);
            Validation::validateInt($marqueId, 'Marque');
            Validation::validateImmatriculation($immatriculation, 'Immatriculation');

            // Nettoyage des données
            $modele = Validation::nettoyage($modele);
            $place = Validation::nettoyage($place);
            $marqueId = Validation::nettoyage($marqueId);
            $immatriculation = Validation::nettoyage($immatriculation);

            // Récupération de la marque
            $marque = $em->getRepository(Marque::class)->find($marqueId);


            $voiture = new Voiture();
            $voiture->setModele($modele);
            $voiture->setPlace($place);
            $voiture->setMarque($marque);
            $voiture->setImmatriculation($immatriculation);

            $em->persist($voiture);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Voiture ajoutée avec succès',
                'data' => $voiture,
            ], 200);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la voiture',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    #[Route('/deleteVoiture/{id}', name: 'app_delete_voiture', methods: "DELETE")]
    public function deleteVoiture(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $voiture = $em->getRepository(Voiture::class)->find($id);
            
            if (!$voiture) {
                throw new ValidationException('Voiture non trouvée');
            }

            $em->remove($voiture);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Voiture supprimée avec succès',
            ], 200);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la voiture',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
