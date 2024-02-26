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
        Validation::validateNotNull($trajets, 'Trajets introuvables');
        
        $result = array_map(function ($trajet) {
            $conducteurDetails = [
                'id' => $trajet->getConducteur()->getId(),
                'prenom' => $trajet->getConducteur()->getPrenom(),
                'nom' => $trajet->getConducteur()->getNom(),
                'email' => $trajet->getConducteur()->getEmail(),
            ]; 

            // Mapping des passagers
            $passagersDetails = [];
            foreach ($trajet->getPassager() as $passager) {
                $passagersDetails[] = [
                    'id' => $passager->getId(),
                    'prenom' => $passager->getPrenom(),
                    'nom' => $passager->getNom(),
                    'email' => $passager->getEmail(),
                ];
            }

            // Mapping de la ville de départ
            $villeDepartDetails = [
                'id' => $trajet->getDepartVille()->getId(),
                'nom' => $trajet->getDepartVille()->getNom(),
            ];

            // Mapping de la ville d'arrivée
            $villeArriverDetails = [
                'id' => $trajet->getArriverVille()->getId(),
                'nom' => $trajet->getArriverVille()->getNom(),
            ];

            return [
                'id' => $trajet->getId(),
                'kms' => $trajet->getKms(),
                'placesDisponible' => $trajet->getPlacesDisponible(),
                'dateDepart' => $trajet->getDateDepart(),
                'heureDepart' => $trajet->getHeureDepart(),
                'villeDepart' => $villeDepartDetails,
                'villeArriver' => $villeArriverDetails,
                'conducteur' => $conducteurDetails,
                'passager' => $passagersDetails,
                'statut' => $trajet->getStatut(),
            ];
        }, $trajets);
        return $this->json([
            'success' => true,
            'message' => 'liste des trajets',
            'data' => $result,
        ], 200);
    }

    #[Route('/liste/{id}', name: 'app_trajet_liste_personne', methods: "POST")]
    public function listeTrajetParPersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Validation des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::nettoyage($id);

        $conducteur = $em->getRepository(Personne::class)->find($id);
        Validation::validateNotNull($conducteur, 'Conducteur introuvable');

        $trajets = $em->getRepository(Trajet::class)->findBy(['conducteur' => $id]);
        Validation::validateNotNull($trajets, 'Trajets introuvables');

        $result = array_map(function ($trajet) {
            $conducteurDetails = [
                'id' => $trajet->getConducteur()->getId(),
                'prenom' => $trajet->getConducteur()->getPrenom(),
                'nom' => $trajet->getConducteur()->getNom(),
                'email' => $trajet->getConducteur()->getEmail(),
            ]; 

            // Mapping des passagers
            $passagersDetails = [];
            foreach ($trajet->getPassager() as $passager) {
                $passagersDetails[] = [
                    'id' => $passager->getId(),
                    'prenom' => $passager->getPrenom(),
                    'nom' => $passager->getNom(),
                    'email' => $passager->getEmail(),
                ];
            }

            // Mapping de la ville de départ
            $villeDepartDetails = [
                'id' => $trajet->getDepartVille()->getId(),
                'nom' => $trajet->getDepartVille()->getNom(),
                'codePostal' => $trajet->getDepartVille()->getCodePostal(),
            ];

            // Mapping de la ville d'arrivée
            $villeArriverDetails = [
                'id' => $trajet->getArriverVille()->getId(),
                'nom' => $trajet->getArriverVille()->getNom(),
                'codePostal' => $trajet->getArriverVille()->getCodePostal(),
            ];

            return [
                'id' => $trajet->getId(),
                'kms' => $trajet->getKms(),
                'placesDisponible' => $trajet->getPlacesDisponible(),
                'dateDepart' => $trajet->getDateDepart(),
                'heureDepart' => $trajet->getHeureDepart(),
                'villeDepart' => $villeDepartDetails,
                'villeArriver' => $villeArriverDetails,
                'conducteur' => $conducteurDetails,
                'passager' => $passagersDetails,
                'statut' => $trajet->getStatut(),
            ];
        }, $trajets);
        return $this->json([
            'success' => true,
            'message' => 'liste des trajets du conducteur',
            'data' => $result,
        ], 200);
    }

    #[Route('/recherche/{villeDepart}/{villeArriver}/{dateDepart}', name: 'app_trajet_recherche', methods: "GET")]
    public function rechercheTrajet(EntityManagerInterface $em, string $villeDepart, string $villeArriver, string $dateDepart): JsonResponse
    {
        // Validation des données
        Validation::validateVille($villeDepart);
        Validation::validateVille($villeArriver);
        Validation::validateDate($dateDepart);

        // Nettoyage des données
        $villeDepart = Validation::nettoyage($villeDepart);
        $villeDepart = Validation::toUpper($villeDepart);
        $villeArriver = Validation::nettoyage($villeArriver);
        $villeArriver = Validation::toUpper($villeArriver);
        $dateDepart = new DateTime($dateDepart);

        // Début de la transaction
        $em->beginTransaction();

        // Trouver des villes
        $villeDepartEntity = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeDepart]);
        Validation::validateNotNull($villeDepartEntity, 'Ville de départ introuvable');
        $villeArriverEntity = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeArriver]);
        Validation::validateNotNull($villeArriverEntity, 'Ville d\'arrivée introuvable');

        // Recherche des trajets
        $trajets = $em->getRepository(Trajet::class)->findBy(['departVille' => $villeDepartEntity->getId(), 'arriverVille' => $villeArriverEntity->getId(), 'dateDepart' => $dateDepart]);
        Validation::validateNotNull($trajets, $dateDepart->format('Y-m-d'));

        // Mapping des résultats
        $result = array_map(fn($trajet) => [
            'id' => $trajet->getId(),
            'kms' => $trajet->getKms(),
            'placesDisponible' => $trajet->getPlacesDisponible(),
            'dateDepart' => $trajet->getDateDepart(),
            'heureDepart' => $trajet->getHeureDepart(),
            'conducteur' => [
                'id' => $trajet->getConducteur()->getId(),
                'prenom' => $trajet->getConducteur()->getPrenom(),
                'nom' => $trajet->getConducteur()->getNom(),
                'email' => $trajet->getConducteur()->getEmail(),
            ],
            'villeDepart' => [
                'id' => $trajet->getDepartVille()->getId(),
                'nom' => $trajet->getDepartVille()->getNom(),
            ],
            'villeArriver' => [
                'id' => $trajet->getArriverVille()->getId(),
                'nom' => $trajet->getArriverVille()->getNom(),
            ],
            
        ], $trajets);

        // Fin de la transaction
        $em->flush();
        $em->commit();

        return $this->json([
            'success' => true,
            'message' => 'Trajets trouvés',
            'data' => $result,
        ], 200);
    }

    #[Route('/insert/{kms}/{idpers}/{dateDepart}/{heureDepart}/{villeDepart}/{villeArriver}/{placesDisponible}', name: 'app_trajet_insert', methods: "POST")]
    public function insertTrajet(int $kms, int $idpers, string $dateDepart, string $heureDepart, string $villeDepart, string $villeArriver, int $placesDisponible, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Validation des données
            Validation::validateInt($kms);
            Validation::validateInt($idpers);
            Validation::validateVille($villeDepart);
            Validation::validateVille($villeArriver);
            Validation::validateInt($placesDisponible);
            Validation::validateDate($dateDepart);
            Validation::validateTime($heureDepart);

            // Nettoyage des données
            $kms = Validation::nettoyage($kms);
            $idpers = Validation::nettoyage($idpers);
            $villeDepart = Validation::nettoyage($villeDepart);
            $villeArriver = Validation::nettoyage($villeArriver);
            $dateDepart = Validation::nettoyage($dateDepart);
            $heureDepart = Validation::nettoyage($heureDepart);
            $placesDisponible = Validation::nettoyage($placesDisponible);

            $em->beginTransaction();

            // Récupération des villes
            $villeDepart = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeDepart]);
            Validation::validateNotNull($villeDepart, 'Ville de départ introuvable');
            $villeArriver = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeArriver]);
            Validation::validateNotNull($villeArriver, 'Ville d\'arrivée introuvable');

            // Récupération de l'utilisateur
            $personne = $em->getRepository(Personne::class)->find($idpers);
            Validation::validateNotNull($personne, 'Utilisateur introuvable');

            // Création du trajet
            $trajet = new Trajet();
            $trajet->setKms($kms);
            $trajet->setConducteur($personne);
            $trajet->setDateDepart(new DateTime($dateDepart));
            $trajet->setHeureDepart(new DateTime($heureDepart));
            $trajet->setDepartVille($villeDepart);
            $trajet->setArriverVille($villeArriver);
            $trajet->setPlacesDisponible($placesDisponible);
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
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'error' => $e->getMessage(),
            ], 400);
        } catch (ORMException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du trajet en base de données',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/update-status/{id}/{status}', name: 'app_trajet_update_status', methods: "PUT")]
    public function updateStatus(int $id, string $status, EntityManagerInterface $em): JsonResponse
    {
        // Validation des données
        Validation::validateInt($id);
        Validation::validateString($status);

        // Nettoyage des données
        $id = Validation::nettoyage($id);
        $status = Validation::nettoyage($status);

        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($id);
        Validation::validateExiste($trajet);

        $trajet->setStatut($status);

        $em->flush();
        return $this->json([
            'success' => true,
            'message' => 'Statut du trajet mis à jour',
        ], 200);
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
