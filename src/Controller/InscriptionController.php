<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\Validation;
use App\Entity\Trajet;
use App\Entity\Personne;

#[route('/inscription')]
class InscriptionController extends AbstractController
{
    // la liste des trajets existants
    #[Route('/', name: 'app_inscription', methods: "GET")]
    public function listeInscription(EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        $trajets = $em->getRepository(Trajet::class)->findAll();

        $result = array_map(function ($trajet) {
            // Conducteur
            $conducteur = $trajet->getConducteur();
            if ($conducteur !== null) {
                $conducteurDetails = [
                    'id' => $conducteur->getId(),
                    'nom' => $conducteur->getNom(),
                    'prenom' => $conducteur->getPrenom(),
                    'email' => $conducteur->getEmail(),
                    'telephone' => $conducteur->getTel(),
                ];
            }

            // Passagers
            $passagers = $trajet->getPassager()->toArray();
            $passagersDetails = array_map(function ($passager) {
                return [
                    'id' => $passager->getId(),
                    'nom' => $passager->getNom(),
                    'prenom' => $passager->getPrenom(),
                    'email' => $passager->getEmail(),
                    'telephone' => $passager->getTel(),
                ];
            }, $passagers);

            // // Villes
            $villeDepart = $trajet->getDepartVille();
            $villeDepartDetails = [
                'id' => $villeDepart->getId(),
                'nom' => $villeDepart->getNom(),
            ];

            $villeArriver = $trajet->getArriverVille();
            $villeArriverDetails = [
                'id' => $villeArriver->getId(),
                'nom' => $villeArriver->getNom(),
            ];

            if ($trajet->getPlacesDisponible() !== null) {
                $places = $trajet->getPlacesDisponible();
                $placesRestantes = $trajet->getPlacesDisponible() - count($passagers);
            }else{
                $places = "inconnu";
                $placesRestantes = "inconnu";
            }

            return [
                'id' => $trajet->getId(),
                'departVille' => $villeDepartDetails,
                'arriverVille' => $villeArriverDetails,
                'dateDepart' => $trajet->getDateDepart(),
                'heureDepart' => $trajet->getHeureDepart(),
                'placesDisponible' => $places,
                'placesRestantes' => $placesRestantes,
                'conducteur' => $conducteurDetails,
                'passagers' => $passagersDetails,
            ];
        }, $trajets);

        return $this->json([
            'success' => true,
            'message' => 'liste des inscriptions',
            'data' => $result,
        ], 200);
    }

    // Le conducteur d'un trajet
    #[Route('/conducteur/{idtrajet}', name: 'app_inscription_liste_conducteur', methods: "GET")]
    public function listeInscriptionConducteur(int $idtrajet, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        Validation::validateInt($idtrajet);

        // Nettoyage des données
        $idtrajet = Validation::nettoyage($idtrajet);

        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');

        // Récupération du conducteur
        $conducteur = $trajet->getConducteur();
        $conducteurDetails = [
            'id' => $conducteur->getId(),
            'nom' => $conducteur->getNom(),
            'prenom' => $conducteur->getPrenom(),
            'email' => $conducteur->getEmail(),
            'telephone' => $conducteur->getTel(),
        ];
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Le conducteur du trajet',
            'data' => $conducteurDetails,
        ], 200);
    }

    // La liste des trajets d'une personne
    #[Route('/listePersonne/{idpers}', name: 'app_inscription_liste_personne', methods: "GET")]
    public function listeInscriptionUser(int $idpers, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($idpers);

        // Nettoyage des données
        $idpers = Validation::nettoyage($idpers);

        // Récupération et vérification de la personne
        $personne = $em->getRepository(Personne::class)->find($idpers);
        Validation::validateExiste($personne, 'Personne introuvable !');

        // Récupération des trajets de la personne
        $trajets = $personne->getTrajets()->toArray();
        $result = array_map(fn($trajet) => [
            'id' => $trajet->getId(),
            'departVille' => $trajet->getDepartVille()->getNom(),
            'arriverVille' => $trajet->getArriverVille()->getNom(),
            'dateDepart' => $trajet->getDateDepart(),
            'heureDepart' => $trajet->getHeureDepart(),
            'placesDisponible' => $trajet->getPlacesDisponible(),
            'placesRestantes' => $trajet->getPlacesDisponible() - count($trajet->getPassager()),
        ], $trajets);

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'liste des trajets de la personne',
            'data' => $result,
        ], 200);
    }

    #[Route('/insert/{idpers}/{idtrajet}', name: 'app_insert_inscription', methods: "POST")]
    public function insertInscription(EntityManagerInterface $em, $idpers, $idtrajet): JsonResponse
    {
        // Récupération et validation des données
        $personne = $em->getRepository(Personne::class)->find($idpers);
        Validation::validateExiste($personne, 'Personne introuvable !');
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');

        // Mis à jour du trajet
        $passagers = $trajet->getPassager();
        Validation::checkPlacesDisponible($trajet->getPlacesDisponible(), count($passagers));
        $trajet->addPassager($personne);
        $em->persist($trajet);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => $trajet,
        ], 200);
    }

    #[Route('/deletePassager/{idtrajet}/{idPassager}', name: 'app_delete_passager', methods: "DELETE")]
    public function deletePassager(int $idtrajet, int $idPassager, EntityManagerInterface $em): JsonResponse
    {
        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');

        // Récupération du passager à supprimer
        $passager = $em->getRepository(Personne::class)->find($idPassager);
        Validation::validateExiste($passager, 'Passager introuvable !');

        // Suppression du passager du trajet
        if ($trajet->getPassager()->contains($passager)) {
            $trajet->removePassager($passager);
            $em->persist($trajet);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Passager introuvable dans le trajet',
            ], 404);
        }
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Passager supprimé du trajet',
        ], 200);
    }

    
}
