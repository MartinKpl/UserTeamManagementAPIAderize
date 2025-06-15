<?php

namespace App\Controller;

use App\Entity\Teams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final class TeamsController extends AbstractController
{
    #[OA\Get(
        path: '/api/teams',
        summary: 'Get a list of teams',
        parameters: [
            new OA\QueryParameter(name: 'page', description: 'Page number', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'limit', description: 'Items per page', required: false, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Teams::class))
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Teams')]
    #[Route('/api/teams', name: 'get_teams', methods: ['GET'])]
    public function getTeams(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $page = $page < 1 ? 1 : $page;

        $limit = $request->query->get('limit', 10);
        $limit = $limit < 1 ? 1 : $limit;

        $offset = ($page - 1) * $limit;

        $teams = $entityManager->getRepository(Teams::class)->findBy([], null, $limit, $offset);

        /*
         * In case you only want team basic details (without users field) uncomment this code
        $teams = array_map(function ($team) {
            return [
                'id'    => $team->getId(),
                'name'  => $team->getName(),
                'created_at' => $team->->getCreatedAt()(),
            ];
        }, $teams);
        */

        return new JsonResponse([
            "data" => $teams,
            "page" => $page,
            "limit" => $limit
        ], 200);
    }

    #[OA\Post(
        path: '/api/teams',
        summary: 'Create a new team',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Team created successfully',
                content: new OA\JsonContent(ref: new Model(type: Teams::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Missing or invalid data'
            )
        ]
    )]
    #[OA\Tag(name: 'Teams')]
    #[Route('/api/teams', name: 'create_team', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || !isset($data['name'])) {
            return new JsonResponse(["error" => "Missing name"], 400);
        }

        $team = new Teams();
        $team->setName($data['name']);
        $team->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($team);
        $entityManager->flush();

        return new JsonResponse($team, 201);
    }

    #[OA\Tag(name: 'Teams')]
    #[Route('/api/teams/{id}', name: 'get_team', methods: ['GET'])]
    public function getTeamDetails(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $team = $entityManager->getRepository(Teams::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for id '. $id
            );
        }

        return new JsonResponse($team);
    }

    #[OA\Put(
        path: '/api/teams/{id}',
        summary: 'Update a team',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'Team ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Team updated successfully',
                content: new OA\JsonContent(ref: new Model(type: Teams::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request data'
            ),
            new OA\Response(
                response: 404,
                description: 'Team not found'
            )
        ]
    )]
    #[OA\Tag(name: 'Teams')]
    #[Route('/api/teams/{id}', name: 'update_team', methods: ['PUT'])]
    public function updateTeam(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null) {
            return new JsonResponse(["error" => "Missing data"], 400);
        }

        $team = $entityManager->getRepository(Teams::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for id '. $id
            );
        }

        if(isset($data['name']) && $data['name'] != $team->getName()) {
            $team->setName($data['name']);
            $entityManager->flush();
        }


        return new JsonResponse($team, 200);
    }

    #[OA\Tag(name: 'Teams')]
    #[Route('/api/teams/{id}', name: 'delete_team', methods: ['DELETE'])]
    public function deleteTeam(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $team = $entityManager->getRepository(Teams::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for id '.$id
            );
        }

        $entityManager->remove($team);
        $entityManager->flush();

        return new JsonResponse($team, 200);
    }
}
