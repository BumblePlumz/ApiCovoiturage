<?php

namespace App\Provider;

use App\Entity\Personne;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Utils\Validation;

class TokenUserProvider implements UserProviderInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function loadPersonneByPseudo(string $pseudo): UserInterface
    {
        $personne = $this->em->getRepository(Personne::class)->findOneBy(['pseudo' => $pseudo]);
        Validation::validateNotNull($personne, 'Utilisateur introuvable avec ce pseudo!');
        return $personne;
    }

    public function refreshUser(UserInterface $personne): UserInterface
    {
        // Validation::validateObjectType($personne, Personne::class);
        if (!$personne instanceof Personne){
            throw new InvalidArgumentException('Mauvais type d\'utilisateur!');
        }
        return $this->loadPersonneByPseudo($personne->getPseudo());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Personne::class;
    }

    public function loadUserByIdentifier(string $token): UserInterface
    {
        $personne = $this->em->getRepository(Personne::class)->findOneBy(['token' => $token]);
        Validation::validateNotNull($personne, 'Utilisateur introuvable lors de l\'authentification!');
        return $personne;
    }
}
