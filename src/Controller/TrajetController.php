<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Trajet;
use App\Utils\NotFoundException;
use App\Utils\Validation;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use App\Entity\Ville;
use App\Entity\Personne;
use App\Utils\ValidationException;

#[Route('/trajet')]
class TrajetController extends AbstractController
{
    #[Route('/liste', name: 'app_trajet_liste', methods: "GET")]
    public function listeTrajet(EntityManagerInterface $em): JsonResponse
    {
        $trajets = $em->getRepository(Trajet::class)->findAll();
        return $this->json([
            'success' => true,
            'message' => 'liste des trajets',
            'data' => $trajets,
        ], 200);
    }

    #[Route('/recherche/{villeDepart}/{villeArriver}/{dateTrajet}', name: 'app_trajet_recherche', methods: "GET")]
    public function rechercheTrajet(EntityManagerInterface $em, string $villeDepart, string $villeArriver, DateTime $dateTrajet): JsonResponse
    {
        // Validation des données
        Validation::validateVille($villeDepart);
        Validation::validateVille($villeArriver);
        Validation::validateDateTime($dateTrajet);

        // Nettoyage des données
        $villeDepart = Validation::nettoyage($villeDepart);
        $villeArriver = Validation::nettoyage($villeArriver);
        $dateTrajet = new DateTime($dateTrajet->format('Y-m-d H:i'));

        // Recherche des trajets
        $trajet = $em->getRepository(Trajet::class)->findBy(['villeDepart' => $villeDepart, 'villeArriver' => $villeArriver, 'dateTrajet' => $dateTrajet]);
        $trajet == null ? $trajet = throw new NotFoundException('Trajet introubable !') : null;

        return $this->json([
            'success' => true,
            'message' => 'Trajets trouvés',
            'data' => $trajet,
        ], 200);
    }

    #[Route('/insert/{kms}/{idpers}/{dateTrajet}/{villeDepart}/{villeArriver}', name: 'app_trajet_insert', methods: "POST")]
    public function insertTrajet(int $kms, int $idpers, DateTime $dateTrajet, string $villeDepart, string $villeArriver, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Validation des données
            Validation::validateInt($kms);
            Validation::validateInt($idpers);
            Validation::validateVille($villeDepart);
            Validation::validateVille($villeArriver);

            // Nettoyage des données
            $kms = Validation::nettoyage($kms);
            $idpers = Validation::nettoyage($idpers);
            $villeDepart = Validation::nettoyage($villeDepart);
            $villeArriver = Validation::nettoyage($villeArriver);

            // Récupération des villes
            $villeDepart = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeDepart]);
            Validation::validateNotNull($villeDepart, 'Ville de départ introuvable');
            $villeArriver = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeArriver]);
            Validation::validateNotNull($villeArriver, 'Ville d\'arrivée introuvable');

            // Récupération de l'utilisateur
            $personne = $em->getRepository(Personne::class)->find($idpers);
            Validation::validateNotNull($personne, 'Utilisateur introuvable');

            // Vérification de la date
            Validation::validateDateTime($dateTrajet);
            $datetime = new DateTime($dateTrajet->format('Y-m-d H:i'));

            // Création du trajet
            $trajet = new Trajet();
            $trajet->setKms($kms);
            $trajet->setConducteur($personne);
            $trajet->setDateDepart($datetime);
            $trajet->setDepartVIlle($villeDepart);
            $trajet->setArriverVille($villeArriver);
            $trajet->setStatut('En Préparation');

            $em->persist($trajet);
            $em->flush();
            $em->commit();

            return $this->json([
                'success' => true,
                'message' => 'Trajet ajouté',
                'data' => $trajet,
            ], 200);
        } catch (ValidationException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'error' => $e->getMessage(),
            ], 400);
        } catch (ORMException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du trajet en base de données',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/delete/{id}', name: 'app_trajet_delete', methods: "DELETE")]
    public function deleteTrajet(int $id, EntityManagerInterface $em): JsonResponse
    {
        $trajet = $em->getRepository(Trajet::class)->find($id);
        Validation::validateExiste($trajet, 'Trajet introuvable');

        $em->remove($trajet);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Trajet supprimé',
        ], 200);
    }
}