<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
final class UsersController extends AbstractController
{
    #[OA\Get(
        path: '/api/users',
        summary: 'Get a list of users',
        parameters: [
            new OA\QueryParameter(name: 'page', description: 'Page number', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'limit', description: 'Items per page', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'name', description: 'User name to search for', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'email', description: 'User email to search for', required: false, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Users::class))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    #[OA\Tag(name: 'Users')]
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

    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'name'],
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created successfully'),
            new OA\Response(response: 400, description: 'Missing name or email'),
            new OA\Response(response: 409, description: 'Email already exists')
        ]
    )]
    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    #[OA\Tag(name: 'Users')]
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
    #[OA\Tag(name: 'Users')]
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

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update an existing user',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'User ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'name', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated successfully'),
            new OA\Response(response: 400, description: 'Missing data'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 409, description: 'Email already exists')
        ]
    )]
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    #[OA\Tag(name: 'Users')]
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
    #[OA\Tag(name: 'Users')]
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
