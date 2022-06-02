<?php

namespace Asciisd\Zoho;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\store\TokenStore;
use com\zoho\crm\api\dc\Environment;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\util\Constants;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Throwable;

class LaravelTokenStore implements TokenStore
{
    /** @var ConnectionInterface */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    private function getEnvName(): string
    {
        return Initializer::getInitializer()->getEnvironment()->getName();
    }

    /**
     * Fetches a token from an environment, user, and client ID.
     * @throws SDKException
     */
    public function getTokenByEnvironmentUserAndClient(Environment $env, UserSignature $user, string $clientId): ?OAuthToken
    {
        try {
            $qb = $this->makeQueryBuilder()
                ->where([
                    'client_id' => $clientId,
                    'env' => $env->getName(),
                    'user_mail' => $user->getEmail(),
                ])
                ->orderByDesc('created_at');
            if (!($row = $qb->first())) {
                return null;
            }

            $token = (new OAuthBuilder)
                ->clientId(data_get($row, 'client_id'))
                ->clientSecret(data_get($row, 'client_secret'))
                ->redirectURL(data_get($row, 'redirect_url'))
                ->build();
            $this->fillTokenFromRow($token, $row);
            $this->fillTokenGrantFromRow($token, $row);

            return $token;
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function getToken(UserSignature $user, OAuthToken $token): ?OAuthToken
    {
        try {
            $qb = $this->makeQueryBuilder();
            if (!($row = $this->addWhereToken($qb, $user->getEmail(), $token)->first())) {
                return null;
            }

            return $this->fillTokenFromRow($token, $row);
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function getTokenById(string $id, OAuthToken $token): OAuthToken
    {
        try {
            if (!($row = $this->makeQueryBuilder()->where(['id' => $id])->first())) {
                throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_BY_ID_DB_ERROR);
            }

            $token->setClientId(data_get($row, 'client_id'));
            $token->setClientSecret(data_get($row, 'client_secret'));
            $token->setRefreshToken(data_get($row, 'refresh_token'));
            $this->fillTokenFromRow($token, $row);

            return $this->fillTokenGrantFromRow($token, $row);
        } catch (SDKException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function saveToken(UserSignature $user, OAuthToken $token): void
    {
        try {
            $token->setUserMail($user->getEmail());
            $this->deleteToken($token);

            $this->makeQueryBuilder()->insert([
                'id' => $token->getId(),
                'env' => $this->getEnvName(),
                'user_mail' => $user->getEmail(),
                'client_id' => $token->getClientId(),
                'client_secret' => $token->getClientSecret(),
                'refresh_token' => $token->getRefreshToken(),
                'access_token' => $token->getAccessToken(),
                'grant_token' => $token->getGrantToken(),
                'expiry_time' => $token->getExpiryTime(),
                'redirect_url' => $token->getRedirectURL(),
            ]);
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::SAVE_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function deleteToken(OAuthToken $token): void
    {
        try {
            $qb = $this->makeQueryBuilder();
            $this->addWhereToken($qb, $token->getUserMail(), $token)
                ->delete();
        } catch (SDKException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKEN_DB_ERROR, null, $ex);
        }
    }

    public function getTokens(): array
    {
        try {
            $result = $this->makeQueryBuilder()->get();

            $tokens = [];
            foreach ($result as $row) {
                $token = (new OAuthBuilder())
                    ->clientId(data_get($row, 'client_id'))
                    ->clientSecret(data_get($row, 'client_secret'))
                    ->refreshToken(data_get($row, 'refresh_token'))
                    ->redirectURL(data_get($row, 'redirect_url'))
                    ->build();
                $this->fillTokenFromRow($token, $row);
                $this->fillTokenGrantFromRow($token, $row);

                $tokens[] = $token;
            }

            return $tokens;
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::GET_TOKENS_DB_ERROR, null, $ex);
        }
    }

    public function deleteTokens()
    {
        try {
            $this->makeQueryBuilder()->delete();
        } catch (Throwable $ex) {
            throw new SDKException(Constants::TOKEN_STORE, Constants::DELETE_TOKENS_DB_ERROR, null, $ex);
        }
    }

    private function makeQueryBuilder(): Builder
    {
        return $this->connection->table('zoho_oauth_tokens');
    }

    /** @throws SDKException */
    private function addWhereToken(Builder $qb, ?string $email, OAuthToken $token): Builder
    {
        if (!$email) {
            throw new SDKException(Constants::USER_MAIL_NULL_ERROR, Constants::USER_MAIL_NULL_ERROR_MESSAGE);
        }

        $qb->where(['user_mail' => $email, 'client_id' => $token->getClientId()]);

        if ($token->getGrantToken() != null) {
            return $qb->where(['grant_token' => $token->getGrantToken()]);
        }

        return $qb->where(['refresh_token' => $token->getRefreshToken()]);
    }

    private function fillTokenFromRow(OAuthToken $token, object $row): OAuthToken
    {
        $token->setId(data_get($row, 'id'));
        $token->setAccessToken(data_get($row, 'access_token'));
        $token->setExpiryTime(new CarbonImmutable(data_get($row, 'expiry_time')));
        $token->setRefreshToken(data_get($row, 'refresh_token'));
        $token->setUserMail(data_get($row, 'user_mail'));

        return $token;
    }

    private function fillTokenGrantFromRow(OAuthToken $token, object $row): OAuthToken
    {
        if (strlen($grantToken = data_get($row, 'grant_token', '')) > 0) {
            $token->setGrantToken($grantToken);
        }

        return $token;
    }
}
