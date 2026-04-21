<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\StatusCode;
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

    #[Route('/sign-up', name: 'app_sign_up_post', methods: ['POST'])]
    #[IsCsrfTokenValid('sign_up', tokenKey: '_csrf_token')]
    public function signUp(
        Request $http_request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $tracer = Globals::tracerProvider()->getTracer('app');

        // 親Span: sign-up処理全体
        $span = $tracer->spanBuilder('sign-up')->startSpan();
        $scope = $span->activate();

        try {
            $email = $http_request->request->get('email');
            $password = $http_request->request->get('password');
            $confirm_password = $http_request->request->get('password_confirm');

            if ($password !== $confirm_password) {
                $span->setStatus(StatusCode::STATUS_ERROR, 'Passwords do not match');
                return $this->render('signup/index.html.twig', [
                    'controller_name' => 'SignUpController',
                    'email' => $email,
                    'errors' => ['Passwords do not match.'],
                ]);
            }

            // 子Span: バリデーション
            $validateSpan = $tracer->spanBuilder('sign-up.validate')->startSpan();
            $validateScope = $validateSpan->activate();
            try {
                $account = new Account();
                $account->setEmail($email);
                $account->setPassword($passwordHasher->hashPassword($account, $password));
                $violations = $validator->validate($account);
            } finally {
                $validateScope->detach();
                $validateSpan->end();
            }

            if (count($violations) > 0) {
                $errors = array_map(fn($v) => $v->getMessage(), iterator_to_array($violations));
                $span->setStatus(StatusCode::STATUS_ERROR, 'Validation failed');
                return $this->render('signup/index.html.twig', [
                    'controller_name' => 'SignUpController',
                    'email' => $email,
                    'errors' => $errors,
                ]);
            }

            // 子Span: DB保存
            $persistSpan = $tracer->spanBuilder('sign-up.persist')->startSpan();
            $persistScope = $persistSpan->activate();
            try {
                $entityManager->persist($account);
                $entityManager->flush();
            } finally {
                $persistScope->detach();
                $persistSpan->end();
            }

            // メトリクス: 登録成功数をカウント
            Globals::meterProvider()
                ->getMeter('app')
                ->createCounter('user.signup.success')
                ->add(1);

            $span->setStatus(StatusCode::STATUS_OK);
            return $this->redirectToRoute('app_login');

        } finally {
            $scope->detach();
            $span->end();
        }
    }
}
