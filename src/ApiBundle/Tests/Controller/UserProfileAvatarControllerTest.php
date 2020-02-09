<?php

namespace ApiBundle\Tests\Controller;

use ApiBundle\Tests\Cases\OAuthJsonApiTestCase;
use AppBundle\Entity\Media;
use Symfony\Component\HttpFoundation\Response;

class UserProfileAvatarControllerTest extends OAuthJsonApiTestCase
{
    public function testAvatarActionSuccess()
    {
        $this->authenticateFixtureUser();

        $media = $this->getEntityManager()->getRepository(Media::class)->findOneBy([]);
        $putData = [
            'user_avatar' => [
                'avatar' => $media->getId(),
            ],
        ];

        $response = $this->client->put('/api/user/profile', ['body' => $putData]);

        $this->assertResponseCode($response, Response::HTTP_NO_CONTENT);
    }

    public function testAvatarActionFailureNoData()
    {
        $this->authenticateFixtureUser();
        $putData = [
            'user_avatar' => [
                'avatar' => '',
            ],
        ];

        $response = $this->client->put('/api/user/profile', ['body' => $putData]);

        $this->assertResponse($response, 'user/avatar/failure_no_data', Response::HTTP_BAD_REQUEST);
    }

    public function testAvatarActionFailureMediaNotExist()
    {
        $this->authenticateFixtureUser();
        $putData = [
            'user_avatar' => [
                'avatar' => 1,
            ],
        ];

        $response = $this->client->put('/api/user/profile', ['body' => $putData]);

        $this->assertResponse($response, 'user/avatar/failure_media_not_exist', Response::HTTP_BAD_REQUEST);
    }
}
