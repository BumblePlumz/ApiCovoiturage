<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Marque;
use App\Utils\Validation;
use App\Utils\NotFoundException;

#[route('/marque')]
class MarqueController extends AbstractController
{
    #[Route('/liste', name: 'app_marque_liste', methods: "GET")]
    public function listeMarque(EntityManagerInterface $em): JsonResponse
    {
        $marques = $em->getRepository(Marque::class)->findAll();
        return $this->json([$marques], 200);
    }

    #[Route('/insert/{nom}', name: 'app_marque_insert', methods: "POST")]
    public function insertMarque($nom, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateMarque($nom);

        // Nettoyer les données
        $nom = Validation::nettoyage($nom);

        // Créer une nouvelle instance de Marque
        $marque = new Marque();
        $marque->setNom($nom);

        // Sauvegarder la marque en base de donnée
        $em->getRepository(Marque::class)->persist($marque);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Marque ajoutée avec succès!',
        ], 201);
    }

    #[Route('/delete/{id}', name: 'app_marque_delete', methods: "DELETE")]
    public function deleteMarque($id, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($id);

        // Nettoyer les données
        $idNettoyer = Validation::nettoyage($id);

        // Trouver la marque par son id
        $marque = $em->getRepository(Marque::class)->find($idNettoyer);

        // Check if the Marque entity exists
        if (!$marque) {
            throw new NotFoundException('Marque non trouvée', 404);
        }

        // Supprimer la marque de la base de donnée
        $em->remove($marque);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Marque supprimée avec succès!',
        ], 200);
    }
}