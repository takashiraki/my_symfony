<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SignInController extends AbstractController
{
    #[Route('/auth/sign-in', name: 'app_auth_sign_in')]
    public function index(): Response
    {
        return $this->render('auth/sign_in/index.html.twig', [
            'controller_name' => 'Auth/SignInController',
        ]);
    }
}
