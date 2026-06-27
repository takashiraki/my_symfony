<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\MoneyDiary;
use App\Entity\PaymentSource;
use App\Entity\User;
use App\Enum\MoneyDiartType;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyDiaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('title')
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('amount')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'query_builder' => fn (CategoryRepository $r) => $r->createQueryBuilder('c')
                    ->where('c.user = :user')->setParameter('user', $user)
                    ->andWhere('c.type = :type')->setParameter('type', $options['type']),
            ]);

        if ($options['type'] === MoneyDiartType::EXPENDITURE) {
            $builder->add('paymentSource', EntityType::class, [
                'class' => PaymentSource::class,
                'choice_label' => 'name',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoneyDiary::class,
            'type' => null,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('type', [
            'null',
            MoneyDiartType::class,
        ]);

        $resolver->setAllowedTypes('user', [
            'null',
            User::class,
        ]);
    }
}
