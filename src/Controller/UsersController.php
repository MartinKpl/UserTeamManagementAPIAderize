<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class UsersController extends AbstractController
{
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $name = $request->query->get('name');
        $email = $request->query->get('email');

        if(!$name and !$email) {
            $page = $request->query->get('page', 1);
            $page = $page < 1 ? 1 : $page;

            $limit = $request->query->get('limit', 10);
            $limit = $limit < 1 ? 1 : $limit;

            $offset = ($page - 1) * $limit;

            $users = $entityManager->getRepository(Users::class)->findBy([], null, $limit, $offset);

            /*
             * In case you only want user basic details (without teams field) uncomment this code
            $users = array_map(function ($user) {
                return [
                    'id'    => $user->getId(),
                    'name'  => $user->getName(),
                    'email' => $user->getEmail(),
                ];
            }, $users);
            */

            return new JsonResponse([
                "data" => $users,
                "page" => $page,
                "limit" => $limit
            ], 200);
        }else{
            $user = null;
            if ($email) {
                $user = $entityManager->getRepository(Users::class)->findUserByEmail($email);
            }else if ($name) {
                $user = $entityManager->getRepository(Users::class)->findUserByLikeName($name);
            }

            if (!$user) {
                throw $this->createNotFoundException(
                    'User not found by ' . ($email ? "email " . $email : "name " . $name)
                );
            }

            return new JsonResponse(["data" => $user]); //to preserve a little bit the structure of the response in case no email or name are provided through query
        }
    }

    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || !isset($data['email']) || !isset($data['name'])) {
            return new JsonResponse(["error" => "Missing name or email"], 400);
        }

        $user = new Users();
        $user->setEmail($data['email']);
        $user->setName($data['name']);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'Email already exists'], 409);
        }

        return new JsonResponse($user, 201);
    }

    #[Route('/api/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserDetails(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '. $id
            );
        }

        return new JsonResponse($user);
    }

//    #[Route('/api/users', name: 'get_user', methods: ['GET'])]
//    public function getUserByEmailOrName(EntityManagerInterface $entityManager, #[MapQueryParameter] string $name, #[MapQueryParameter] string $email): JsonResponse
//    {
//        $user = null;
//        if ($email) {
//            $user = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
//        }else if ($name) {
//            $user = $entityManager->getRepository(Users::class)->findOneBy(['name' => $name]);
//        }
//
//        if (!$user) {
//            throw $this->createNotFoundException(
//                'User not found'
//            );
//        }
//
//        return new JsonResponse(["message" => "Through email name", "user" => $user]);
//    }

    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($data === null) {
            return new JsonResponse(["error" => "Missing data"], 400);
        }

        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '. $id
            );
        }

        if(isset($data['email']) && $data['email'] != $user->getEmail()) {
            $user->setEmail($data['email']);
        }

        if(isset($data['name']) && $data['name'] != $user->getName()) {
            $user->setName($data['name']);
        }

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'Email already exists'], 409);
        }

        return new JsonResponse($user, 200);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$id
            );
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse($user, 200);
    }
}
