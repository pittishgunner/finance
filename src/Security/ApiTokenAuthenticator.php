<?php

namespace App\Security;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class ApiTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * @var string
     */
    public const HEADER_ACCESS_USER = 'Access-user';

    /**
     * @var string
     */
    public const HEADER_ACCESS_TOKEN = 'Access-Token';

    /**
     * @var string
     */
    private const HEADER_AUTHORIZATION = 'Authorization';

    /**
     * @var string
     */
    private const HEADER_BEARER = 'Bearer ';

    public function supports(Request $request): ?bool
    {
        return self::requestHasBearerToken($request)
            || self::requestHasAccessAppAndToken($request);
    }

    /**
     * @throws CustomUserMessageAuthenticationException
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = match (true) {
            self::requestHasBearerToken($request) => self::getApiTokenFromBearerToken($request),
            self::requestHasAccessAppAndToken($request) => self::getApiTokenFromAccessAppAndToken($request),
            default => throw new CustomUserMessageAuthenticationException('Missing authentication token.')
        };

        return new Passport(
            new UserBadge($apiToken->getUser()),
            new CustomCredentials(
                function ($token, UserInterface $user) use ($apiToken) {
                    return (
                        $apiToken->getUser() === $user->__toString() &&
                        $apiToken->getToken() === $user->getPassword()
                    );
                },
                $apiToken->getToken()
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }

    private static function requestHasAccessAppAndToken(Request $request): bool
    {
        return $request->headers->has(self::HEADER_ACCESS_USER)
            && $request->headers->has(self::HEADER_ACCESS_TOKEN);
    }

    private static function requestHasBearerToken(Request $request): bool
    {
        return $request->headers->has(self::HEADER_AUTHORIZATION)
            && str_starts_with((string) $request->headers->get(self::HEADER_AUTHORIZATION), self::HEADER_BEARER);
    }

    private static function getApiTokenFromAccessAppAndToken(Request $request): ApiToken
    {
        return new ApiToken(
            user: (string) $request->headers->get(self::HEADER_ACCESS_USER),
            token: (string) $request->headers->get(self::HEADER_ACCESS_TOKEN)
        );
    }

    private static function getApiTokenFromBearerToken(Request $request): ApiToken
    {
        $authorizationHeader = (string) $request->headers->get(self::HEADER_AUTHORIZATION);

        return new ApiToken(
            user: 'api',
            token: substr($authorizationHeader, strlen(self::HEADER_BEARER))
        );
    }
}
