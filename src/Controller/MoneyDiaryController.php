<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\MoneyDiary;
use App\Enum\MoneyDiartType;
use App\Form\MoneyDiaryType;
use App\Repository\MoneyDiaryRepository;
use App\Security\Voter\MoneyDiaryVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/money-diary')]
#[IsGranted('ROLE_USER')]
final class MoneyDiaryController extends AbstractController
{
    #[Route(name: 'app_money_diary_index', methods: ['GET'])]
    public function index(MoneyDiaryRepository $moneyDiaryRepository): Response
    {
        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();

        $income = $moneyDiaryRepository->sumByType($user, MoneyDiartType::INCOME);
        $expense = $moneyDiaryRepository->sumByType($user, MoneyDiartType::EXPENDITURE);

        return $this->render('money_diary/index.html.twig', [
            'money_diaries' => $moneyDiaryRepository->findByUser($user),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ]);
    }

    #[Route('/expense/new', name: 'app_money_diary_expense_new', methods: ['GET', 'POST'])]
    public function newExpense(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moneyDiary = new MoneyDiary();

        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();
        $form = $this->createForm(MoneyDiaryType::class, $moneyDiary, [
            'type' => MoneyDiartType::EXPENDITURE,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $moneyDiary->setUser($user);
            $moneyDiary->setType(MoneyDiartType::EXPENDITURE);
            $entityManager->persist($moneyDiary);
            $entityManager->flush();

            return $this->redirectToRoute('app_money_diary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_diary/new.html.twig', [
            'money_diary' => $moneyDiary,
            'form' => $form,
        ]);
    }

    #[Route('/income/new', name: 'app_money_diary_income_new', methods: ['GET', 'POST'])]
    public function newIncome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moneyDiary = new MoneyDiary();

        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();
        $form = $this->createForm(MoneyDiaryType::class, $moneyDiary, [
            'type' => MoneyDiartType::INCOME,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $moneyDiary->setUser($user);
            $moneyDiary->setType(MoneyDiartType::INCOME);
            $entityManager->persist($moneyDiary);
            $entityManager->flush();

            return $this->redirectToRoute('app_money_diary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_diary/new.html.twig', [
            'money_diary' => $moneyDiary,
            'form' => $form,
        ]);
    }

    #[Route('/expense/{id}', name: 'app_money_diary_show', methods: ['GET'])]
    public function show(MoneyDiary $moneyDiary): Response
    {
        $this->denyAccessUnlessGranted(MoneyDiaryVoter::VIEW, $moneyDiary);

        return $this->render('money_diary/show.html.twig', [
            'money_diary' => $moneyDiary,
        ]);
    }

    #[Route('/expense/{id}/edit', name: 'app_money_diary_expense_edit', methods: ['GET', 'POST'])]
    public function editExpense(Request $request, MoneyDiary $moneyDiary, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(MoneyDiaryVoter::EDIT, $moneyDiary);

        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();
        $form = $this->createForm(MoneyDiaryType::class, $moneyDiary, [
            'type' => MoneyDiartType::EXPENDITURE,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_money_diary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_diary/edit.html.twig', [
            'money_diary' => $moneyDiary,
            'form' => $form,
        ]);
    }

    #[Route('/income/{id}/edit', name: 'app_money_diary_income_edit', methods: ['GET', 'POST'])]
    public function editIncome(Request $request, MoneyDiary $moneyDiary, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(MoneyDiaryVoter::EDIT, $moneyDiary);

        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();
        $form = $this->createForm(MoneyDiaryType::class, $moneyDiary, [
            'type' => MoneyDiartType::INCOME,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_money_diary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_diary/edit.html.twig', [
            'money_diary' => $moneyDiary,
            'form' => $form,
        ]);
    }

    #[Route('/expense/{id}', name: 'app_money_diary_delete', methods: ['POST'])]
    public function delete(Request $request, MoneyDiary $moneyDiary, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(MoneyDiaryVoter::DELETE, $moneyDiary);

        if ($this->isCsrfTokenValid('delete' . $moneyDiary->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($moneyDiary);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_money_diary_index', [], Response::HTTP_SEE_OTHER);
    }
}
