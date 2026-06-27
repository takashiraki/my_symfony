<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\PaymentSource;
use App\Form\PaymentSourceType;
use App\Repository\PaymentSourceRepository;
use App\Security\Voter\PaymentSourceVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment/source')]
#[IsGranted('ROLE_USER')]
final class PaymentSourceController extends AbstractController
{
    #[Route(name: 'app_payment_source_index', methods: ['GET'])]
    public function index(PaymentSourceRepository $paymentSourceRepository): Response
    {
        /** @var Account $account */
        $account = $this->getUser();

        return $this->render('payment_source/index.html.twig', [
            'payment_sources' => $paymentSourceRepository->findByUser($account->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_payment_source_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paymentSource = new PaymentSource();
        $form = $this->createForm(PaymentSourceType::class, $paymentSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Account $account */
            $account = $this->getUser();
            $paymentSource->setUser($account->getUser());

            $entityManager->persist($paymentSource);
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_source_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment_source/new.html.twig', [
            'payment_source' => $paymentSource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_source_show', methods: ['GET'])]
    public function show(PaymentSource $paymentSource): Response
    {
        $this->denyAccessUnlessGranted(PaymentSourceVoter::VIEW, $paymentSource);

        return $this->render('payment_source/show.html.twig', [
            'payment_source' => $paymentSource,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_source_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaymentSource $paymentSource, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PaymentSourceVoter::EDIT, $paymentSource);

        $form = $this->createForm(PaymentSourceType::class, $paymentSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_source_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment_source/edit.html.twig', [
            'payment_source' => $paymentSource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_source_delete', methods: ['POST'])]
    public function delete(Request $request, PaymentSource $paymentSource, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PaymentSourceVoter::DELETE, $paymentSource);

        if ($this->isCsrfTokenValid('delete' . $paymentSource->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($paymentSource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_source_index', [], Response::HTTP_SEE_OTHER);
    }
}
