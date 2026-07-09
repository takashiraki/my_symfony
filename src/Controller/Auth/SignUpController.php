<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\Account;
use App\Event\AccountRegisterdEvent;
use App\Form\AccountType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\ByteString;

final class SignUpController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Verification of register
     * Input email or click the Google auth button
     *
     * @see https://symfony.com/doc/current/security/csrf.html
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    #[Route('/sign-up', name: 'app_sign_up', methods: ['GET', 'POST'])]
    public function verification(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $account = $entityManager->getRepository(Account::class)->findOneBy(
                [
                    'email' => $email,
                ]
            );

            if ($account) {
                return $this->redirectToRoute('app_login');
            }

            $account = new Account();
            $account->setEmail($email);

            $verifyCode = ByteString::fromRandom(36)->toString();
            $account->setVerifyCode($verifyCode);

            $baseUrl = $request->getSchemeAndHttpHost();
            $verification_url = $this->generateUrl(
                'app_sign_up_verify_code',
                [
                    'code' => $verifyCode,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $mail = new TemplatedEmail()
                ->from('no-reply@hogehoge.com')
                ->to($email)
                ->subject('Please verify your email')
                ->htmlTemplate('mail/verification.html.twig')
                ->context([
                    'verification_url' => $verification_url,
                ]);

            try {
                $entityManager->persist($account);
                $entityManager->flush();
                $mailer->send($mail);
            } catch (Exception $e) {
                dd($e);
            }

            return $this->redirectToRoute('app_sign_up_verification');
        }
        return $this->render('signup/index.html.twig', [
            'countries' => Countries::getNames('en'),
        ]);
    }

    /**
     * Shown after the verification email has been sent
     */
    #[Route('/sign-up/verification', name: 'app_sign_up_verification', methods: ['GET'])]
    public function verificationSent(): Response
    {
        return $this->render('signup/verification.html.twig');
    }

    #[Route('/sign-up/verify-code', name: 'app_sign_up_verify_code', methods: ['GET'])]
    public function verifyCode(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $account = $entityManager->getRepository(Account::class)->findOneBy(
            [
                'verify_code' => $request->query->get('code'),
            ]
        );

        if (! $account) {
            return $this->redirectToRoute('app_sign_up');
        }

        $account->setVerifyCode(null);
        $account->setVerifiedAt(new DateTime());

        $entityManager->persist($account);
        $entityManager->flush();

        $request->getSession()->set('verified_account_id', $account->getId());
        return $this->redirectToRoute('app_register');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security
    ): Response {
        $accountId = $request->getSession()->get('verified_account_id');

        if (! $accountId) {
            return $this->redirectToRoute('app_sign_up');
        }

        $account = $entityManager->getRepository(Account::class)->find($accountId);

        if (! $account) {
            return $this->redirectToRoute('app_sign_up');
        }

        $isOauth = $account->getOauthProvider() === 'google';
        $form = $this->createForm(
            AccountType::class,
            $account,
            [
                'require_password' => ! $isOauth,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('password')) {
                $plainPassword = $form->get('password')->getData();

                $account->setPassword($userPasswordHasher->hashPassword($account, $plainPassword));
            }
            $entityManager->flush();

            $request->getSession()->remove('verified_account_id');

            $security->login($account, 'form_login');

            $event = new AccountRegisterdEvent($account);

            $this->eventDispatcher->dispatch($event);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('signup/register.html.twig', [
            'form' => $form,
            'account' => $account,
        ]);
    }
}
