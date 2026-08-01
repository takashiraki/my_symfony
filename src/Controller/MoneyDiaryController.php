<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\MoneyDiary;
use App\Enum\MoneyDiartType;
use App\Form\MoneyDiaryType;
use App\Repository\MoneyDiaryRepository;
use App\Security\Voter\MoneyDiaryVoter;
use DateTimeImmutable;
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
    public function index(MoneyDiaryRepository $moneyDiaryRepository, Request $httpRequest): Response
    {
        /** @var Account $account */
        $account = $this->getUser();
        $user = $account->getUser();

        $start = DateTimeImmutable::createFromFormat(
            '!Ym',
            $httpRequest->query->get('month') ?? new DateTimeImmutable()->format('Ym')
        );
        $end = $start->modify('last day of this month')->setTime(23, 59, 59);

        $income = $moneyDiaryRepository
            ->sumByType(
                $user,
                MoneyDiartType::INCOME,
                $start,
                $end
            );
        $expense = $moneyDiaryRepository
            ->sumByType(
                $user,
                MoneyDiartType::EXPENDITURE,
                $start,
                $end
            );

        $prevMonth = $start->modify('-1 month');
        $nextMonth = $start->modify('+1 month');

        // dd($moneyDiaryRepository->findByUser($user, $start, $end), $start, $end);

        return $this->render('money_diary/index.html.twig', [
            'money_diaries' => $moneyDiaryRepository->findByUser($user, $start, $end),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'current_month' => $start,
            'prev_month' => $prevMonth,
            'next_month' => $nextMonth,
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
