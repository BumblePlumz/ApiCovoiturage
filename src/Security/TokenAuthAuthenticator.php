<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use App\Provider\TokenUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Utils\Validation;
use App\Entity\Personne;
use Symfony\Component\Security\Core\Exception\DisabledException;

class TokenAuthAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private EntityManagerInterface $em;
    private TokenUserProvider $tokenUserProvider;
    private Security $security;


    public function __construct(EntityManagerInterface $em, TokenUserProvider $tokenUserProvider, Security $security)
    {
        $this->em = $em;
        $this->tokenUserProvider = $tokenUserProvider;
        $this->security = $security;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function getPersonneFromToken(?string $token): UserInterface
    {
        // Vérification du token
        Validation::validateNotNull($token, 'Token manquant!');

        // Récupération de l'utilisateur à partir du token
        try {
            $user = $this->tokenUserProvider->loadUserByIdentifier($token);
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException("Failed to load user from token: " . $e->getMessage());
        }
        return $user;
    }

    public function authenticate(Request $request): Passport
    {
        // Récupération du token
        $apiToken = $this->getTokenFromRequest($request);

        return new SelfValidatingPassport(new UserBadge($apiToken));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];
        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        /*
            * If you would like this class to control what happens when an anonymous user accesses a
            * protected page (e.g. redirect to /login), uncomment this method and make this class
            * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
            *
            * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
            */
        $content = ['erreur' => 'Vous devez être authentifié pour accéder à cette ressource!'];
        return new Response(json_encode($content), Response::HTTP_UNAUTHORIZED);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('Le token api est manquant!');
        }
        return $apiToken;
    }
}
