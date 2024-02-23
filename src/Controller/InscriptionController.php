<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\NotFoundException;
use App\Utils\Validation;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use App\Entity\Trajet;
use App\Entity\Personne;
use App\Utils\ValidationException;

#[route('/inscription')]
class InscriptionController extends AbstractController
{
    #[Route('/', name: 'app_inscription', methods: "GET")]
    public function listeInscription(EntityManagerInterface $em): JsonResponse
    {
        // Récupération des inscriptions avec les personnes et les trajets associés
        $personneRepository = $em->getRepository(Personne::class);
        $query = $personneRepository->createQueryBuilder('p')
            ->leftJoin('p.trajets', 't')
            ->getQuery();
        $personnesAvecTrajets = $query->getResult();

        return $this->json([
            'success' => true,
            'message' => 'liste des inscriptions',
            'data' => $personnesAvecTrajets,
        ], 200);
    }

    #[Route('/listeConducteur/{idtrajet}', name: 'app_inscription_liste_conducteur', methods: "GET")]
    public function listeInscriptionConducteur(int $idtrajet, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        Validation::validateInt($idtrajet);

        // Nettoyage des données
        $idtrajet = Validation::nettoyage($idtrajet);

        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');
        $conducteur = $trajet->getConducteur();
        $em->flush();
        return $this->json([
            'success' => true,
            'message' => 'liste des conducteurs',
            'data' => $conducteur,
        ], 200);
    }

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
        $trajetsDeLaPersonne = $personne->getTrajets();
        $em->flush();
        return $this->json([
            'success' => true,
            'message' => 'liste des trajets de la personne',
            'data' => $trajetsDeLaPersonne,
        ], 200);
    }
    #[Route('/insert/{idpers}/{idtrajet}', name: 'app_insert_inscription', methods: "POST")]
    public function insertInscription(EntityManagerInterface $em, $idpers, $idtrajet): JsonResponse
    {
        // Récupération et validation des données
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');
        $personne = $em->getRepository(Personne::class)->find($idpers);
        Validation::validateExiste($personne, 'Personne introuvable !');

        // Mis à jour du trajet
        $trajet->setConducteur($personne);
        $em->persist($trajet);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => $trajet,
        ], 200);
    }
    
    #[Route('/delete/{idtrajet}', name: 'app_delete_passager', methods: "DELETE")]
    public function deletePassager(int $idtrajet, int $idpassager, EntityManagerInterface $em): JsonResponse
    {
        // Récupération du trajet
        $trajet = $em->getRepository(Trajet::class)->find($idtrajet);
        Validation::validateExiste($trajet, 'Trajet introuvable !');

        // Récupération du passager à supprimer
        $passager = $em->getRepository(Personne::class)->find($idpassager);
        Validation::validateExiste($passager, 'Passager introuvable !');

        // Suppression du passager du trajet
        $trajet->removePassager($passager);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Passager supprimé du trajet',
        ], 200);
    }
}