<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private RequestValidationManager $requestValidationManager,
        private Security $security
    )
    {
    }

    public function getAllUsers()
    {
        return $this->userRepository->findAll();
    }

    public function getUser($id)
    {
        return $this->userRepository->find($id);
    }

    public function createUser(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $validateRequest = $this->requestValidationManager->validateRequest(['login', 'pass', 'phone'], $data);

        if($validateRequest !== true){
           return $validateRequest;
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setPass($this->passwordHasher->hashPassword($user, $data['pass']));
        $user->setPhone($data['phone']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return ['errors' => $errorMessages];
        }
        $this->entityManager->persist($user);
        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return ['errors' => ['This login is already taken.']];
        }

        return $user;
    }

    public function updateUser($request, User $user): array|User
    {
        $data = json_decode($request->getContent(), true);
        $validateRequest = $this->requestValidationManager->validateRequest(['login', 'pass', 'phone'], $data);

        if($validateRequest !== true){
            return $validateRequest;
        }

        if(isset($data['roles']) && !in_array('ROLE_ADMIN', $this->security->getToken()->getUser()->getRoles())){
            unset($data['roles']);
        }

        $user->setLogin($data['login']);
        $user->setPass($this->passwordHasher->hashPassword($user, $data['pass']));

        $this->serializer->deserialize(json_encode($data), User::class, 'json', [
            'groups' => 'user:put',
            'object_to_populate' => $user,
        ]);

        if ($user->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPass($hashedPassword);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return ['errors' => $errorMessages];
        }

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return ['errors' => ['This login is already taken.']];
        }
        return $user;

    }

    public function deleteUser($userId)
    {
        $user = $this->userRepository->find($userId);
        if($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return true;
        }else{
            throw new UserNotFoundException("User Not found!", 404);
        }
    }
}