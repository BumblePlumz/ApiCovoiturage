<?php

namespace App\Controller;

use App\Utils\Validation;
use Doctrine\ORM\EntityManager;
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
use PDOException;

#[Route('/personne')]
class PersonneController extends AbstractController
{
    #[Route('/liste', name: 'app_personne')]
    public function liste(EntityManagerInterface $em): JsonResponse
    {
        try {
            $personnes = $em->getRepository(Personne::class)->findAll();
            return $this->json([
                'success' => true,
                'message' => 'Liste des personnes', 
                'data' => $personnes
            ], 200);
        }catch(PDOException $e){
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des personnes",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/inscription/{login}/{password}', name: 'app_personne_inscription', methods: ['POST'])]
    public function inscription(Request $request, EntityManagerInterface $em, $login, $password): JsonResponse
    {
        try {
            // Réception des données
            // $pseudo = $request->request->get('login');
            // $password = $request->request->get('password');

            $pseudo = $login;

            // Vérication des données
            Validation::validateUsername($pseudo);
            // Validation::validatePassword($password);

            // Nettoyage des données
            $pseudo = Validation::nettoyage($pseudo);
            $password = Validation::nettoyage($password);

            // Création de l'objet
            $personne = new Personne();
            $personne->setPseudo($pseudo);
            $personne->setPassword(password_hash($password, PASSWORD_BCRYPT));
            $personne->setRoles(['ROLE_USER']);

            // Insertion en base de données
            $em->persist($personne);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Personne enregistrée avec succès!',
            ], 200);
        } catch (ValidationException | PDOException $e) {
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de l'inscription",
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/insert', name: 'app_personne_insert', methods: "POST")]
    public function insertPersonne(Request $request, EntityManagerInterface $em): JsonResponse
    {

        try {
            // Réception des données
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $tel = $request->request->get('tel');
            $email = $request->request->get('email');
            $ville = $request->request->get('ville');
            $modele = $request->request->get('modele');
            $nbPlaces = $request->request->get('nbPlaces');
            $immatriculation = $request->request->get('immatriculation');
            $nomMarque = $request->request->get('nomMarque');

            // Vérication des données
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateVille($ville);
            Validation::validateMarque($nomMarque);
            Validation::validateString($modele);
            Validation::validateInt($nbPlaces);
            Validation::validateImmatriculation($immatriculation);

            // Nettoyage des données
            $prenom = Validation::validateExiste($prenom) ? Validation::nettoyage($prenom) : null;
            $nom = Validation::validateExiste($nom) ? Validation::nettoyage($nom) : null;
            $tel = Validation::validateExiste($tel) ? Validation::nettoyage($tel) : null;
            $email = Validation::validateExiste($email) ? Validation::nettoyage($email) : null;
            $ville = Validation::validateExiste($ville) ? Validation::nettoyage($ville) : null;
            $nomMarque = Validation::validateExiste($nomMarque) ? Validation::nettoyage($nomMarque) : null;
            $modele = Validation::validateExiste($modele) ? Validation::nettoyage($modele) : null;
            $nbplaces = Validation::validateExiste($nbPlaces) ? Validation::nettoyage($nbPlaces) : null;
            $immatriculation = Validation::validateExiste($immatriculation) ? Validation::nettoyage($immatriculation) : null;

            // Début de la transaction
            $em->beginTransaction();

            // Création de l'objet Marque
            $marque = new Marque();
            $marque->setNom($nomMarque);

            // Création de l'objet Voiture
            $voiture = new Voiture();
            $voiture->setModele($modele);
            $voiture->setPlace($nbplaces);
            $voiture->setImmatriculation($immatriculation);
            $voiture->setMarque($marque);

            // Création de l'objet Ville
            $ville = new Ville();
            $ville->setNom($nom);

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
            ], 200);
        } catch (ValidationException | PDOException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de l'insertion",
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/delete/{id}', name: 'app_personne_delete', methods: "DELETE")]
    public function deletePersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $personne = $em->getRepository(Personne::class)->find($id);

            if (!$personne) {
                throw new ValidationException("Personne inexistant!");
            }

            $em->remove($personne);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Personne deleted successfully!',
            ]);
        } catch (ValidationException | PDOException $e) {
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la suppression",
                'error' => $e->getMessage(),
            ],404);
        }
    }

    #[Route('/select/{id}', name: 'app_personne_select', methods: "GET")]
    public function selectPersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérication des données
            Validation::validateInt($id);

            // Nettoyage des données
            $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;

            $personne = $em->getRepository(Personne::class)->find($id);

            if (!$personne) {
                throw new ValidationException("Personne not found!");
            }

            return $this->json([
                'success' => true,
                'personne' => $personne,
            ], 200);
        } catch (ValidationException | PDOException $e) {
            return $this->json([
                'success' => false,
                'message' => "Error retrieving personne",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/update/{id}', name: 'app_update_personne', methods: "PUT")]
    public function updatePersonne(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Réception des données
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $tel = $request->request->get('tel');
            $email = $request->request->get('email');
            $ville = $request->request->get('ville');
            $marque = $request->request->get('marque');
            $modele = $request->request->get('modele');
            $nbPlaces = $request->request->get('nbPlaces');
            $immatriculation = $request->request->get('immatriculation');

            // Vérication des données
            Validation::validateInt($id);
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateVille($ville);
            Validation::validateMarque($marque);
            Validation::validateString($modele);
            Validation::validateInt($nbPlaces);
            Validation::validateImmatriculation($immatriculation);

            // Nettoyage des données
            $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
            $prenom = Validation::validateExiste($prenom) ? Validation::nettoyage($prenom) : null;
            $nom = Validation::validateExiste($nom) ? Validation::nettoyage($nom) : null;
            $tel = Validation::validateExiste($tel) ? Validation::nettoyage($tel) : null;
            $email = Validation::validateExiste($email) ? Validation::nettoyage($email) : null;
            $ville = Validation::validateExiste($ville) ? Validation::nettoyage($ville) : null;
            $marque = Validation::validateExiste($marque) ? Validation::nettoyage($marque) : null;
            $modele = Validation::validateExiste($modele) ? Validation::nettoyage($modele) : null;
            $nbplaces = Validation::validateExiste($nbPlaces) ? Validation::nettoyage($nbPlaces) : null;
            $immatriculation = Validation::validateExiste($immatriculation) ? Validation::nettoyage($immatriculation) : null;

            $personne = $em->getRepository(Personne::class)->find($id);

            if (!$personne) {
                throw new ValidationException("Personne not found!");
            }

            // Récupérer les données de la requête
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les propriétés de l'objet Personne
            $personne->setPrenom($data['prenom']);
            $personne->setNom($data['nom']);
            $personne->setTel($data['tel']);
            $personne->setEmail($data['email']);

            // Enregistrer les modifications en base de données
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Personne updated successfully!',
            ]);
        } catch (ValidationException | PDOException $e) {
            return $this->json([
                'success' => false,
                'message' => "Error updating personne",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
