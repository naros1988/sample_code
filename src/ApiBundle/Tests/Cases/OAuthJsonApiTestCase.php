<?php

namespace ApiBundle\Tests\Cases;

use OAuth2\OAuth2;

abstract class OAuthJsonApiTestCase extends JsonApiTestCase
{
    protected function authenticateFixtureUser(string $userFixturePath = 'cases/oauth_user.yml', $username = 'test')
    {
        $this->loadFixturesFromFile($userFixturePath);
        $this->loadFixturesFromFile('cases/oauth_client.yml');

        /** @var Client $client */
        $client = $this->getEntityManager()->getRepository(Client::class)->findOneBy(['randomId' => 'test_id']);
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $username]);

        /** @var OAuth2 $oauthServer */
        $oauthServer = $this->getService('fos_oauth_server.server');

        $token = $oauthServer->createAccessToken($client, $user, 'password', AccessToken::EXPIRATION_TIME);
        $accessToken = $token['access_token'];

        self::$staticClient->setDefaultOption('headers/Authorization', 'Bearer '.$accessToken);

        return $accessToken;
    }
}
