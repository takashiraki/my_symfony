<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['require_password']) {
            $builder->add('password', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 8),
                ],
            ]);
        }
        $builder->add('user', UserType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
            'require_password' => true,
        ]);
    }
}
