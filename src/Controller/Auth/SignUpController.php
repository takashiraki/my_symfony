<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SignUpController extends AbstractController
{
    #[Route('/sign-up', name: 'app_sign_up', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('signup/index.html.twig', [
            'controller_name' => 'SignUpController',
        ]);
    }

    /**
     * Sign up
     *
     * @see https://symfony.com/doc/current/security/csrf.html
     *
     * @param Request $http_request
     *
     * @return Response
     */
    #[Route('/sign-up', name: 'app_sign_up_post', methods: ['POST'])]
    #[IsCsrfTokenValid('sign_up', tokenKey: '_csrf_token')]
    public function signUp(
        Request $http_request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $email = $http_request->request->get('email');
        $password = $http_request->request->get('password');
        $confirm_password = $http_request->request->get('password_confirm');
        if ($password !== $confirm_password) {
            return $this->render('signup/index.html.twig', [
                'controller_name' => 'SignUpController',
                'email' => $email,
                'errors' => ['Passwords do not match.'],
            ]);
        }

        $account = new Account();
        $account->setEmail($email);
        $account->setPassword($passwordHasher->hashPassword($account, $password));
        $violations = $validator->validate($account);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->render('signup/index.html.twig', [
                'controller_name' => 'SignUpController',
                'email' => $email,
                'errors' => $errors,
            ]);
        }

        $entityManager->persist($account);
        $entityManager->flush();

        return $this->redirectToRoute('app_login');
    }
}
