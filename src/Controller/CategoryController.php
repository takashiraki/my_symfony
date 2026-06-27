<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Category;
use App\Enum\CategoryType as EnumCategoryType;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Security\Voter\CategoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/category')]
#[IsGranted('ROLE_USER')]
final class CategoryController extends AbstractController
{
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function indexExpense(CategoryRepository $categoryRepository): Response
    {
        /** @var Account $account */
        $account = $this->getUser();

        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findByUser($account->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_category_new_choice_type', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('category/new.html.twig');
    }

    #[Route('/new/expense', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function newExpense(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Account $account */
            $account = $this->getUser();
            $category->setUser($account->getUser());
            $category->setType(EnumCategoryType::EXPENDITURE);

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new-expense.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/new/income', name: 'app_category_new_income', methods: ['GET', 'POST'])]
    public function newIncome(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Account $account */
            $account = $this->getUser();
            $category->setUser($account->getUser());
            $category->setType(EnumCategoryType::INCOME);

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new-income.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function showExpense(Category $category): Response
    {
        $this->denyAccessUnlessGranted(CategoryVoter::VIEW, $category);

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function editExpense(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CategoryVoter::EDIT, $category);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function deleteExpense(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CategoryVoter::DELETE, $category);

        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
