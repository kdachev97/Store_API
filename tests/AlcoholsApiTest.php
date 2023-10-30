<?php

namespace App\Tests;

use App\Entity\Alcohol;
use App\Entity\Producer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AlcoholsApiTest extends WebTestCase
{
    protected function createAuthenticatedClient($email = "krum@example.com", $password = "password")
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "email" => $email,
                "password" => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testUpdateAlcohol()
    {
        $client = static::createAuthenticatedClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);
        $client->request(
            'PUT',
            '/alcohols/' . $alcohol->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "name" => "Jameson 1"
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            '{
                "name":"Jameson 1",
                "type":"whiskey",
                "description":"Tennessee whiskey",
                "producer":{
                    "name":"Bacardi",
                    "country":"Cuba"
                },
                "abv":37.5,
                "image":{
                    "name":"Jameson",
                    "url":"sameUrl.com"
                }
            }',
            $client->getResponse()->getContent()
        );
    }

    public function testGetOneAlcohol()
    {
        $client = static::createAuthenticatedClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);

        $client->request('GET', '/alcohols/' . $alcohol->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{
                "name":"Jameson",
                "type":"whiskey",
                "description":"Tennessee whiskey",
                "producer":{
                    "name":"Bacardi",
                    "country":"Cuba"
                },
                "abv":37.5,
                "image":{
                    "name":"Jameson",
                    "url":"sameUrl.com"
                }
            }',
            $client->getResponse()->getContent()
        );
    }

    public function testGetAlcoholsUnauthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/alcohols', ['page' => 1]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(50, $content['total']);
    }

    public function testGetAlcohols()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/alcohols', ['page' => 1]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(50, $content['total']);
    }

    public function testGetAlcoholsFilters()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/alcohols',
            [
                'page' => 1,
                'type' => 'whiskey',
                'name' => 'Jameson'
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(1, $content['total']);
    }


    public function testGetOneAlcoholWrongIDFail()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/alcohols/fa5e2591-0463-40c4-a32a-62a89df22549');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneAlcoholUnauthenticatedSuccess()
    {
        $client = $this->createClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);

        $client->request('GET', '/alcohols/' . $alcohol->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateAlcoholUnauthorizedFail()
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $producerRepository = $entityManager->getRepository(Producer::class);
        $producer = $producerRepository->findOneBy(['name' => 'Bacardi']);
        $client->request(
            'POST',
            '/alcohols',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "name" => "Jameson 1",
                "type" => "whiskey",
                "description" => "Tennessee whiskey",
                "producer" => $producer->getId(),
                "abv" => 37.5,
                "image" => [
                    "name" => "Jameson 1",
                    "url" => "sameUrls.com"
                ]
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateAlcohol()
    {
        $client = static::createAuthenticatedClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $producerRepository = $entityManager->getRepository(Producer::class);
        $producer = $producerRepository->findOneBy(['name' => 'Bacardi']);

        $client->request(
            'POST',
            '/alcohols',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "name" => "Jameson 5",
                "type" => "whiskey",
                "description" => "Tennessee whiskey",
                "producer" => $producer->getId(),
                "abv" => 37.5,
                "image" => [
                    "name" => "Jameson 5",
                    "url" => "sameUrls.com"
                ]
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{
                "name":"Jameson 5",
                "type":"whiskey",
                "description":"Tennessee whiskey",
                "producer":{
                    "name":"Bacardi",
                    "country":"Cuba"
                },
                "abv":37.5,
                "image":{
                "name":"Jameson 5",
                "url":"sameUrls.com"
                }
            }',
            $client->getResponse()->getContent()
        );
    }


    public function testUpdateAlcoholUnauthorizedFail()
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);

        $client->request(
            'PUT',
            '/alcohols/' . $alcohol->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "name" => "Jameson 1"
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteAlcohol()
    {
        $client = static::createAuthenticatedClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);
        $client->request(
            'DELETE',
            '/alcohols/' . $alcohol->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testDeleteAlcoholUnauthenticatedFail()
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $alcoholRepository = $entityManager->getRepository(Alcohol::class);
        $alcohol = $alcoholRepository->findOneBy(['name' => 'Jameson']);
        $client->request(
            'DELETE',
            '/alcohols/' . $alcohol->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
