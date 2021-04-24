<?php

declare(strict_types=1);

namespace DanielDeWit\LighthouseSanctum\GraphQL\Mutations;

use DanielDeWit\LighthouseSanctum\Exceptions\HasApiTokensException;
use Exception;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Translation\Translator;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use RuntimeException;

class Logout
{
    protected AuthFactory $authFactory;
    protected Translator $translator;

    public function __construct(AuthFactory $authFactory, Translator $translator)
    {
        $this->authFactory = $authFactory;
        $this->translator  = $translator;
    }

    /**
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return array<string, string|array>
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        $user = $this->authFactory
            ->guard('sanctum')
            ->user();

        if (! $user) {
            throw new RuntimeException('Unable to detect current user.');
        }

        if (! $user instanceof HasApiTokens) {
            throw new HasApiTokensException($user);
        }

        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = $user->currentAccessToken();
        $personalAccessToken->delete();

        return [
            'status'  => 'TOKEN_REVOKED',
            'message' => $this->translator->get('Your session has been terminated'),
        ];
    }
}
