<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ville;
use App\Utils\Validation;
use App\Utils\ValidationException;

class VilleController extends AbstractController
{
    #[Route('/insertVille/{nom}/{cp}', name: 'app_insert_ville', methods: "POST")]
    public function insertVille(string $nom, string $cp, EntityManagerInterface $em): JsonResponse
    {
        try {

            // Vérification des données
            Validation::validateVille($nom);
            Validation::validateCodePostal($cp);

            // Nettoyage des données
            $villeNettoyer = Validation::nettoyage($nom);
            $cpNettoyer = Validation::nettoyage($cp);

            // Création de l'objet
            $ville = new Ville();
            $ville->setNom($villeNettoyer);
            $ville->setCodePostal($cpNettoyer);
            // Insertion en base de données
            $em->persist($ville);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ville ajoutée',
            ], 200);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/deleteVille/{id}', name: 'app_ville', methods: "DELETE")]
    public function deleteVille(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérification des données
            Validation::validateInt($id);

            // Nettoyage des données
            $idNettoyer = Validation::nettoyage($id);

            // Suppression de l'objet
            $em->getRepository(Ville::class)->delete($idNettoyer);
            $em->flush();
            return $this->json([
                'success' => true,
                'message' => 'Ville supprimée',
            ]);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/listeVille', name: 'app_liste_ville', methods: "GET")]
    public function listeVIlle(EntityManagerInterface $em): JsonResponse
    {
        $villes = $em->getRepository(Ville::class)->findAll();
        return $this->json($villes, 200);
    }
    #[Route('/listeCodePostal', name: 'app_liste_codepostal', methods: "GET")]
    public function listeCodePostal(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VilleController.php',
        ]);
    }
}
