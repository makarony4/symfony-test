<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/v1/api/users', name: 'user_')]
final class UserController extends AbstractController
{
    public function __construct(
        private UserManager $userManager,
        private SerializerInterface $serializer,
    )
    {
    }

    #[Route('', name: 'get_users', methods: ['GET'])]
    public function getUserCollection(): JsonResponse
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return new JsonResponse(["error" => "Access Denied"], Response::HTTP_FORBIDDEN);
        }
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $data = $this->serializer->serialize($this->userManager->getAllUsers(), 'json', ['groups' => 'user:get-output']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {

        $user = $this->userManager->getUser($id);

        if(!$user){
            return new JsonResponse(["error" => "User not found."], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:get-output']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return new JsonResponse(["error" => "Access Denied"], Response::HTTP_FORBIDDEN);
        }
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $response = $this->userManager->createUser($request);
        if($response instanceof UserInterface){
            $userData = $this->serializer->serialize($response, 'json', ['groups' => 'user:post-output']);
            return new JsonResponse($userData, 201, [], true);
        }

        return new JsonResponse($response, 400, []);

    }

    #[Route('/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);
        try {
            $response = $this->userManager->updateUser($request, $user);
            if ($response instanceof UserInterface) {
                $userData = $this->serializer->serialize($response, 'json', ['groups' => 'user:put-output']);
                return new JsonResponse($userData, 201, [], true);
            }

        }catch (\Throwable $e){
            return new JsonResponse(["error" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($response, 400, []);

    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser($id): JsonResponse
    {

        $user = $this->userManager->getUser($id);
        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);

        if(!$user){
            return new JsonResponse(["error" => "User not found."], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->userManager->deleteUser($user->getId());
            return new JsonResponse(null, 204);
        } catch (\Throwable $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
