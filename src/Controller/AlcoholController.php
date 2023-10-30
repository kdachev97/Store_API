<?php

namespace App\Controller;

use App\Entity\Alcohol;
use App\Repository\AlcoholRepository;
use App\Repository\ProducerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AlcoholController extends AbstractController
{
    public function __construct(
        private AlcoholRepository $alcoholRepository,
        private EntityManagerInterface $entityManager,
        private ProducerRepository $producerRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Api route to get alcohols
     */
    #[Route('/alcohols', name: 'get_alcohols_collection', methods: ["GET"])]
    public function getCollection(Request $request)
    {
        $page = $request->query->get('page');
        $perPage = $request->query->get('perPage') ?? 25;
        $name = $request->query->get('name');
        $type = $request->query->get('type');

        if (!$page) {
            throw new BadRequestHttpException("Query parameter 'page' is missing.");
        }

        $alcohols = $this->alcoholRepository->findAllWithOptionalFilters($name, $type, $page, $perPage);

        return $this->json(
            [
                'total' => $alcohols['total'],
                'items' => $alcohols['items'],
            ],
            200,
            [],
            ['groups' => ['alcohol']]
        );
    }

    /**
     * Api route to get a single alcohol
     */
    #[Route('/alcohols/{id}', name: 'get_alcohol', methods: ["GET"])]
    public function get($id)
    {
        $alcohol = $this->alcoholRepository->find($id);
        if ($alcohol === null) {
            throw new NotFoundHttpException("Not found");
        }

        return $this->json(
            $alcohol,
            200,
            [],
            ['groups' => ['alcohol']]
        );
    }

    /**
     * Api route to get a add a new alcohol
     */
    #[Route('/alcohols', name: 'alcohols_create', methods: ['POST'])]
    public function createAlcohol(Request $request): JsonResponse
    {
        $requestArray = json_decode($request->getContent(), true);
        try {
            $alcohol = $this->serializer->deserialize(
                $request->getContent(),
                Alcohol::class,
                'json',
                [AbstractNormalizer::IGNORED_ATTRIBUTES => ['producer']]
            );
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => 'Invalid JSON payload',
            ], 400);
        }

        $producer = $this->producerRepository->find($requestArray['producer']);
        $alcohol->setProducer($producer);

        $violations = $this->validator->validate($alcohol);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $message = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage()
                ];
                array_push($messages, $message);
            }

            return $this->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $messages,
            ], 400);
        }

        $this->entityManager->persist($alcohol);
        $this->entityManager->flush();

        return $this->json($alcohol, 201, [], [
            'groups' => ['alcohol']
        ]);
    }

    /**
     * Api route to update an existing alcohol
     */
    #[Route('/alcohols/{id}', name: 'alcohols_update', methods: ['PUT'])]
    public function updateAlcohol(Request $request, string $id): JsonResponse
    {
        $alcohol = $this->alcoholRepository->find($id);
        if ($alcohol === null) {
            throw new NotFoundHttpException("Not found");
        }

        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Alcohol::class,
                'json',
                ['object_to_populate' => $alcohol]
            );
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => 'Invalid JSON payload',
            ], 400);
        }

        $violations = $this->validator->validate($alcohol);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $message = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage()
                ];
                array_push($messages, $message);
            }

            return $this->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $messages,
            ], 400);
        }

        $this->entityManager->flush();

        return $this->json($alcohol, 200, [], [
            'groups' => ['alcohol']
        ]);
    }
    /**
     * Api route to delete a single alcohol
     */
    #[Route('/alcohols/{id}', name: 'delete_alcohol', methods: ['DELETE'])]
    public function deleteAlcohol(string $id): JsonResponse
    {
        $alcohol = $this->alcoholRepository->find($id);
        if ($alcohol === null) {
            throw new NotFoundHttpException("Not found");
        }

        $this->entityManager->remove($alcohol);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
