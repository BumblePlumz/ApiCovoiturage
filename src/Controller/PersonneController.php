<?php

namespace App\Controller;

use App\Utils\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Personne;
use App\Utils\ValidationException;
use App\Entity\Marque;
use App\Entity\Voiture;
use App\Entity\Ville;
use Doctrine\ORM\Exception\ORMException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/personne')]
class PersonneController extends AbstractController
{
    #[Route('/liste', name: 'app_personne_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $em): JsonResponse
    {
        $personnes = $em->getRepository(Personne::class)->findAll();

        $result = array_map(function ($personne) {
            $voitureDetails = null;
            $voiture = $personne->getVoiture();
            if ($voiture !== null) {
                $marqueDetails = null;
                $marque = $voiture->getMarque();
                if ($marque !== null) {
                    $marqueDetails = [
                        'id' => $marque->getId(),
                        'nom' => $marque->getNom(),
                    ];
                }
        
                $voitureDetails = [
                    'id' => $voiture->getId(),
                    'marque' => $marqueDetails,
                    'modele' => $voiture->getModele(),
                ];
            }
        
            return [
                'id' => $personne->getId(),
                'pseudo' => $personne->getPseudo(),
                'prenom' => $personne->getPrenom(),
                'nom' => $personne->getNom(),
                'tel' => $personne->getTel(),
                'email' => $personne->getEmail(),
                'ville' => $personne->getVille(),
                'voiture' => $voitureDetails,
                'isActif' => $personne->isIsActif(),
            ];
        }, $personnes);
        

        return $this->json([
            'success' => true,
            'message' => 'Liste des personnes',
            'data' => $result,
        ], 200);
    }

    #[Route('/liste/inactif', name: 'app_personne_liste_inactif', methods: ['GET'])]
    public function listeInactif(EntityManagerInterface $em): JsonResponse
    {
        $personnes = $em->getRepository(Personne::class)->findBy(['isActif' => false]);

        $result = array_map(function ($personne) {
            $voitureDetails = null;
            $voiture = $personne->getVoiture();
            if ($voiture !== null) {
                $marqueDetails = null;
                $marque = $voiture->getMarque();
                if ($marque !== null) {
                    $marqueDetails = [
                        'id' => $marque->getId(),
                        'nom' => $marque->getNom(),
                    ];
                }
        
                $voitureDetails = [
                    'id' => $voiture->getId(),
                    'marque' => $marqueDetails,
                    'modele' => $voiture->getModele(),
                ];
            }
        
            return [
                'id' => $personne->getId(),
                'pseudo' => $personne->getPseudo(),
                'prenom' => $personne->getPrenom(),
                'nom' => $personne->getNom(),
                'tel' => $personne->getTel(),
                'email' => $personne->getEmail(),
                'ville' => $personne->getVille(),
                'voiture' => $voitureDetails,
                'isActif' => $personne->isIsActif(),
            ];
        }, $personnes);

        return $this->json([
            'success' => true,
            'message' => 'Liste des personnes inactives',
            'data' => $result
        ], 200);
    }

    #[Route('/login/{username}/{password}', name: 'app_personne_login', methods: ['POST'])]
    public function login(EntityManagerInterface $em, $username, $password, JWTTokenManagerInterface $jwtManager, Request $request): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($username);
        // TODO : réactiver la validation du mot de passe
        // Validation::validatePassword($password);

        // Nettoyage des données
        $username = Validation::nettoyage($username);
        $password = Validation::nettoyage($password);

        // Recherche de l'utilisateur en base de données
        $personne = $em->getRepository(Personne::class)->findOneBy(['pseudo' => $username]);
        Validation::validateExiste($personne) ? null : throw new ValidationException("Pseudo ou mot de passe incorrect!");

        // Vérification du mot de passe
        Validation::validateBcryptPassword($password, $personne->getPassword());

        // Génération et stockage du token
        $token = $jwtManager->create($personne);
        $personne->setToken($token);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'token' => $token,
                'user' => $personne->getid(),
            ]
        ], 200);
    }

    #[Route('/inscription/{username}/{password}', name: 'app_personne_inscription', methods: ['POST'])]
    public function inscription(Request $request, EntityManagerInterface $em, $username, $password): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($username);
        // TODO : réactiver la validation du mot de passe
        //Validation::validatePassword($password);

        // Nettoyage des données
        $username = Validation::nettoyage($username);
        $password = Validation::nettoyage($password);

        // Création de l'objet
        $personne = new Personne();
        $personne->setPseudo($username);
        $personne->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $personne->setRoles(['ROLE_USER']);

        // Insertion en base de données
        $em->persist($personne);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Personne enregistrée avec succès!',
        ], 201);
    }

    #[Route('/insert/{prenom}/{nom}/{tel}/{email}/{ville}/{voitureId}', name: 'app_personne_insert', methods: "POST")]
    public function insertPersonne(EntityManagerInterface $em, $prenom, $nom, $tel, $email, $ville, $voitureId): JsonResponse
    {
        // TODO : récupérer les données de session utilisateur
        try {
            // Vérication des données
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateVille($ville);
            Validation::validateInt($voitureId);

            // Nettoyage des données
            $prenom = Validation::nettoyage($prenom);
            $nom = Validation::nettoyage($nom);
            $tel = Validation::nettoyage($tel);
            $email = Validation::nettoyage($email);
            $ville = Validation::nettoyage($ville);
            $ville = Validation::toUpper($ville);
            $voitureId = Validation::nettoyage($voitureId);

            // Début de la transaction
            $em->beginTransaction();

            // Création de l'objet Voiture
            $voiture = $em->getRepository(Voiture::class)->find($voitureId);
            Validation::validateNotNull($voiture, $voitureId);

            // Création de l'objet Ville
            $ville = $em->getRepository(Ville::class)->findOneBy(['nom' => $ville]);
            Validation::validateNotNull($ville, $ville);

            // Création de l'objet Personne
            $personne = new Personne();
            $personne->setPrenom($prenom);
            $personne->setNom($nom);
            $personne->setTel($tel);
            $personne->setEmail($email);
            $personne->setVille($ville);
            $personne->setVoiture($voiture);

            $em->persist($personne);
            $em->flush();
            $em->commit();
            return $this->json([
                'success' => true,
                'message' => 'Personne enregistrée avec succès!',
            ], 201);
        } catch (ValidationException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], $e->getCode());
        } catch (ORMException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de l'insertion en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/select/{id}', name: 'app_personne_select', methods: "POST")]
    public function selectPersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Vérication des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;

        // Récupération de l'objet Personne
        $personne = $em->getRepository(Personne::class)->find($id);
        Validation::validateExiste($personne);
        Validation::validateNotNull($personne);
        
        $result = [
            'id' => $personne->getId(),
            'pseudo' => $personne->getPseudo(),
            'prenom' => $personne->getPrenom(),
            'nom' => $personne->getNom(),
            'tel' => $personne->getTel(),
            'email' => $personne->getEmail(),
            'ville' => $personne->getVille(),
            'voiture' => $personne->getVoiture(),
            'isActif' => $personne->isIsActif(),
        ];

        Validation::validateExiste($personne) ? null : throw new ValidationException("Personne inexistant!");

        return $this->json([
            'success' => true,
            'message' => 'Personne trouvée avec succès!',
            'personne' => $result,
        ], 200);
    }   

    // TODO : changer pour une request body afin de traiter les cas nulls.
    #[Route('/update/{id}/{prenom}/{nom}/{tel}/{email}/{marque}/{modele}/{nbPlaces}/{immatriculation}', name: 'app_update_personne', methods: "PUT")]
    public function updatePersonne(int $id, ?string $prenom, ?string $nom, ?string $tel, ?string $email, ?string $marque, ?string $modele, ?int $nbPlaces, ?string $immatriculation, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérication des données
            Validation::validateInt($id);
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateMarque($marque);
            Validation::validateModele($modele);
            Validation::validateInt($nbPlaces);
            Validation::validateImmatriculation($immatriculation);

            // Nettoyage des données
            $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
            $prenom = Validation::validateExiste($prenom) ? Validation::nettoyage($prenom) : null;
            $nom = Validation::validateExiste($nom) ? Validation::nettoyage($nom) : null;
            $tel = Validation::validateExiste($tel) ? Validation::nettoyage($tel) : null;
            $email = Validation::validateExiste($email) ? Validation::nettoyage($email) : null;
            $marque = Validation::validateExiste($marque) ? Validation::nettoyage($marque) : null;
            $modele = Validation::validateExiste($modele) ? Validation::nettoyage($modele) : null;
            $nbPlaces = Validation::validateExiste($nbPlaces) ? Validation::nettoyage($nbPlaces) : null;
            $immactriculation = Validation::validateExiste($immatriculation) ? Validation::nettoyage($immatriculation) : null;

            // Début de la transaction
            $em->beginTransaction();

            // Récupération de l'objet Personne
            $personne = $em->getRepository(Personne::class)->findPersonneWithDependenciesById($id);
            Validation::validateNotNull($personne);

            // Mettre à jour les propriétés de l'objet Personne si les données sont différentes de null
            $prenom == null ? null : $personne->setPrenom($prenom);
            $nom == null ? null : $personne->setNom($nom);
            $tel == null ? null : $personne->setTel($tel);
            $email == null ? null : $personne->setEmail($email);

            // Mettre à jour les propriétés de l'objet Voiture si les données sont différentes de null
            if ($personne->getVoiture() == null){
                $voiture = new Voiture();
                $modele == null ? null : $voiture->setModele($modele);
                $nbPlaces == null ? null : $voiture->setPlace($nbPlaces);
                $immactriculation == null ? null : $voiture->setImmatriculation($immactriculation);
                $marqueEntity = $em->getRepository(Marque::class)->findOneBy(['nom' => $marque]);
                if ($marqueEntity == null){
                    $marqueObject = new Marque();
                    $marque == null ? null : $marqueObject->setNom($marque);
                    $voiture->setMarque($marqueObject);
                }else{
                    $voiture->setMarque($marqueEntity);
                }
                $personne->setVoiture($voiture);
            } else {
                $voiture = $personne->getVoiture();
                $modele == null ? null : $voiture->setModele($modele);
                $nbPlaces == null ? null : $voiture->setPlace($nbPlaces);
            }

            // Enregistrer les modifications en base de données
            $em->flush();

            // Valider la transaction
            $em->commit();

            return $this->json([
                'success' => true,
                'message' => 'Personne updated successfully!',
            ]);
        } catch (ValidationException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], 409);
        } catch (ORMException $e) {
            if ($em->getConnection()->isTransactionActive()) {
                $em->rollback();
            }
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la mise à jour en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/update-actif/{id}/{isActif}', name: 'app_update_personne_actif', methods: "PUT")]
    public function updatePersonneActif(int $id, int $isActif, EntityManagerInterface $em): JsonResponse
    {
        // Vérification des données
        Validation::validateInt($id);
        Validation::validateInt($isActif);

        // Nettoyage des données
        $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
        $isActif = Validation::validateExiste($isActif) ? Validation::nettoyage($isActif) : null;

        // Récupération de l'objet Personne
        $personne = $em->getRepository(Personne::class)->find($id);
        Validation::validateNotNull($personne);

        // Mettre à jour le champ isActif de l'objet Personne si la donnée est différente de null
        $personne->setIsActif($isActif == 1);

        // Enregistrer les modifications en base de données
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Champ isActif de la personne mis à jour avec succès!',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_personne_delete', methods: "DELETE")]
    public function deletePersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données
        $personne = $em->getRepository(Personne::class)->find($id);

        // Vérification que la personne existe        
        Validation::validateExiste($personne) ? null : throw new ValidationException("Personne inexistant!");

        $em->remove($personne);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'La personne a été supprimée avec succès!',
        ], 201);
    }
}
