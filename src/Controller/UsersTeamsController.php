<?php

namespace App\Controller;

use App\Entity\Teams;
use App\Entity\Users;
use App\Entity\UsersTeams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UsersTeamsController extends AbstractController
{
    #[Route('/api/teams/{team_id}/members', name: 'add_users_teams', methods: ['POST'])]
    public function addUserToTeam(Request $request, EntityManagerInterface $entityManager,int $team_id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || !isset($data['role']) || !isset($data['user_id'])) {
            return new JsonResponse(["error" => "Missing role or User id"], 400);
        }

        $user = $entityManager->getRepository(Users::class)->find($data['user_id']);

        if($user === null) {
            return new JsonResponse(["error" => "User not found"], 404);
        }

        $team = $entityManager->getRepository(Teams::class)->find($team_id);

        if($team === null) {
            return new JsonResponse(["error" => "Team not found"], 404);
        }

        $userTeam = $entityManager->getRepository(UsersTeams::class)->createQueryBuilder("ut")
            ->join("ut.user_id", "u")
            ->join("ut.team_id", "t")
            ->where('u.id = :userId')
            ->andWhere('t.id = :teamId')
            ->setParameter('userId', $user->getId())
            ->setParameter('teamId', $team->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if($userTeam !== null) {
            return new JsonResponse(["error" => "User " . $user->getName() . " is already in team " . $team->getName()], 404); #Should this be a 404 HTTP error code? Could just be 200 and do nothing.
        }

        $userTeam = new UsersTeams();
        $userTeam->addUserId($user);
        $userTeam->addTeamId($team);
        $userTeam->setRole($data['role']);

        $entityManager->persist($userTeam);
        $entityManager->flush();

        return new JsonResponse($userTeam, 201);
    }

    #[Route('/api/teams/{team_id}/members/{user_id}', name: 'update_role', methods: ['PUT'])]
    public function updateRoleOfUserInTeam(Request $request, EntityManagerInterface $entityManager,int $team_id, int $user_id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || !isset($data['role'])) {
            return new JsonResponse(["error" => "Missing role"], 400);
        }

        $user = $entityManager->getRepository(Users::class)->find($user_id);

        if($user === null) {
            return new JsonResponse(["error" => "User not found"], 404);
        }

        $team = $entityManager->getRepository(Teams::class)->find($team_id);

        if($team === null) {
            return new JsonResponse(["error" => "Team not found"], 404);
        }

        $userTeam = $entityManager->getRepository(UsersTeams::class)->createQueryBuilder("ut")
            ->join("ut.user_id", "u")
            ->join("ut.team_id", "t")
            ->where('u.id = :userId')
            ->andWhere('t.id = :teamId')
            ->setParameter('userId', $user_id)
            ->setParameter('teamId', $team_id)
            ->getQuery()
            ->getOneOrNullResult();

        if($userTeam === null) {
            return new JsonResponse(["error" => "User " . $user->getName() . " not found in team " . $team->getName()], 404);
        }

        $userTeam->setRole($data['role']);

        $entityManager->flush();

        return new JsonResponse($userTeam, 200);
    }

    #[Route('/api/teams/{team_id}/members/{user_id}', name: 'remove_user_from_team', methods: ['DELETE'])]
    public function removeUserFromTeam(EntityManagerInterface $entityManager,int $team_id, int $user_id): JsonResponse
    {
        $user = $entityManager->getRepository(Users::class)->find($user_id);

        if($user === null) {
            return new JsonResponse(["error" => "User not found"], 404);
        }

        $team = $entityManager->getRepository(Teams::class)->find($team_id);

        if($team === null) {
            return new JsonResponse(["error" => "Team not found"], 404);
        }

        $userTeam = $entityManager->getRepository(UsersTeams::class)->createQueryBuilder("ut")
            ->join("ut.user_id", "u")
            ->join("ut.team_id", "t")
            ->where('u.id = :userId')
            ->andWhere('t.id = :teamId')
            ->setParameter('userId', $user_id)
            ->setParameter('teamId', $team_id)
            ->getQuery()
            ->getOneOrNullResult();

        if($userTeam === null) {
            return new JsonResponse(["error" => "User " . $user->getName() . " not found in team " . $team->getName()], 404);
        }

        $entityManager->remove($userTeam);
        $entityManager->flush();

        return new JsonResponse($userTeam, 200);
    }
}
