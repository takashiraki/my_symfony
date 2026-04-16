<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SignUpController extends AbstractController
{
    #[Route('/sign-up', name: 'app_sign_up', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('sign_up/index.html.twig', [
            'controller_name' => 'SignUpController',
        ]);
    }

    /**
     * Sign up
     * 
     * @see https://symfony.com/doc/current/security/csrf.html
     * @param Request $http_request
     * @return Response
     */
    #[Route('/sign-up', name: 'app_sign_up_post', methods: ['POST'])]
    public function signUp(Request $http_request): Response
    {
        $email = $http_request->request->get('email');
        $password = $http_request->request->get('password');
        $confirm_password = $http_request->request->get('password_confirm');
    }
}
