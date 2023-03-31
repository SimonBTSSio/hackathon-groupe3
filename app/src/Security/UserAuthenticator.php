<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private $userRepository;

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $user = $this->userRepository->findOneBy(array('email' => $email)); // Find the user in the database based on the email address

        if (!$user || !$user->isVerified()) {
            throw new AuthenticationException('User is not verified.');
        }
        
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
  
        $user = $token->getUser(); // Find the user in the database based on the email address

        if (implode($user->getRoles()) == "ROLE_ADMIN") {
            return new RedirectResponse($this->urlGenerator->generate('back_default_index'));
        } elseif (implode($user->getRoles()) == "ROLE_MEMBER") {
            return new RedirectResponse($this->urlGenerator->generate('front_default_index'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate('doctor_default_index'));
        }
        
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        
        return new RedirectResponse($this->urlGenerator->generate('app_front'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
