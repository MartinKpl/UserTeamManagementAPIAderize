<?php

namespace App\Controller;

use App\Entity\Teams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TeamsController extends AbstractController
{
    #[Route('/api/teams', name: 'get_teams', methods: ['GET'])]
    public function getTeams(EntityManagerInterface $entityManager): JsonResponse
    {
        $teams = $entityManager->getRepository(Teams::class)->findAll();

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

        return new JsonResponse($teams, 200);
    }

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
