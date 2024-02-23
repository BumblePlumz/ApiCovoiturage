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

#[Route('/personne')]
class PersonneController extends AbstractController
{
    #[Route('/liste', name: 'app_personne_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $em): JsonResponse
    {
        $personnes = $em->getRepository(Personne::class)->findAll();
        return $this->json([
            'success' => true,
            'message' => 'Liste des personnes',
            'data' => $personnes
        ], 200);
    }
    
    #[Route('/login/{login}/{password}', name: 'app_personne_login', methods: ['POST'])]
    public function login(EntityManagerInterface $em, $login, $password): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($login);
        Validation::validatePassword($password);

        // Nettoyage des données
        $login = Validation::nettoyage($login);
        $password = Validation::nettoyage($password);

        // Recherche de l'utilisateur en base de données
        $personne = $em->getRepository(Personne::class)->findOneBy(['login' => $login]);
        Validation::validateExiste($personne) ? null : throw new ValidationException("Utilisateur inexistant!");
        
        // Vérification du mot de passe
        Validation::validateBcryptPassword($password, $personne->getPassword());

        return $this->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => $personne,
        ], 200);
    }
    

    #[Route('/inscription/{login}/{password}', name: 'app_personne_inscription', methods: ['POST'])]
    public function inscription(Request $request, EntityManagerInterface $em, $login, $password): JsonResponse
    {
        // Vérication des données
        Validation::validateUsername($login);
        Validation::validatePassword($password);

        // Nettoyage des données
        $login = Validation::nettoyage($login);
        $password = Validation::nettoyage($password);

        // Création de l'objet
        $personne = new Personne();
        $personne->setPseudo($login);
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
            ], 201);
        } catch (ValidationException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], $e->getCode());
        } catch (ORMException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de l'insertion en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
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

    #[Route('/select/{id}', name: 'app_personne_select', methods: "GET")]
    public function selectPersonne(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Vérication des données
        Validation::validateInt($id);

        // Nettoyage des données
        $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;

        $personne = $em->getRepository(Personne::class)->find($id);

        Validation::validateExiste($personne) ? null : throw new ValidationException("Personne inexistant!");

        return $this->json([
            'success' => true,
            'message' => 'Personne trouvée avec succès!',
            'personne' => $personne,
        ], 200);
    }

    #[Route('/update/{id}/{prenom}/{nom}/{tel}/{email}/{marque}/{modele}/{nbPlaces}', name: 'app_update_personne', methods: "PUT")]
    public function updatePersonne(int $id, string $prenom, string $nom, string $tel, string $email, string $marque, string $modele, int $nbPlaces, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Vérication des données
            Validation::validateInt($id);
            Validation::validateString($prenom);
            Validation::validateString($nom);
            Validation::validateTelephone($tel);
            Validation::validateEmail($email);
            Validation::validateMarque($marque);
            Validation::validateString($modele);
            Validation::validateInt($nbPlaces);

            // Nettoyage des données
            $id = Validation::validateExiste($id) ? Validation::nettoyage($id) : null;
            $prenom = Validation::validateExiste($prenom) ? Validation::nettoyage($prenom) : null;
            $nom = Validation::validateExiste($nom) ? Validation::nettoyage($nom) : null;
            $tel = Validation::validateExiste($tel) ? Validation::nettoyage($tel) : null;
            $email = Validation::validateExiste($email) ? Validation::nettoyage($email) : null;
            $marque = Validation::validateExiste($marque) ? Validation::nettoyage($marque) : null;
            $modele = Validation::validateExiste($modele) ? Validation::nettoyage($modele) : null;
            $nbplaces = Validation::validateExiste($nbPlaces) ? Validation::nettoyage($nbPlaces) : null;

            // Début de la transaction
            $em->beginTransaction();

            // Récupération de l'objet Personne
            $personne = $em->getRepository(Personne::class)->find($id);
            Validation::validateExiste($personne) ? null : throw new ValidationException("Personne inexistant!");

            // Mettre à jour les propriétés de l'objet Personne si les données sont différentes de null
            $prenom == null ? null : $personne->setPrenom($prenom);
            $nom == null ? null : $personne->setNom($nom);
            $tel == null ? null : $personne->setTel($tel);
            $email == null ? null : $personne->setEmail($email);
            $marque == null ? null : $personne->getVoiture()->getMarque()->setNom($marque);
            $modele == null ? null : $personne->getVoiture()->setModele($modele);
            $nbplaces == null ? null : $personne->getVoiture()->setPlace($nbplaces);

            // Enregistrer les modifications en base de données
            $em->flush();

            // Valider la transaction
            $em->commit();

            return $this->json([
                'success' => true,
                'message' => 'Personne updated successfully!',
            ]);
        } catch (ValidationException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la validation des données",
                'error' => $e->getMessage(),
            ], 500);
        } catch (ORMException $e) {
            $em->rollback();
            return $this->json([
                'success' => false,
                'message' => "Erreur lors de la mise à jour en base de données",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}