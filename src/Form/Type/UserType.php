<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('realName')
            ->add('plainPassword', PasswordType::class, ['label' => 'Password', 'required' => false, 'mapped' => false])
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'choices'  => [
                        'Watcher' => 'ROLE_WATCHER',
                        'Committer' => 'ROLE_COMMITTER',
                        'Admin' => 'ROLE_ADMIN',
                    ]
                ]
            )
//            ->add('githubId', TextType::class, ['required' => false])
//            ->add('googleId', TextType::class, ['required' => false])
//            ->add('facebookId', TextType::class, ['required' => false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'empty_data' => fn(FormInterface $form): User => new User(
                    email: $form->get('email')->getData(),
                    realName: $form->get('realName')->getData(),
                    roles: $form->get('roles')->getData()
                )
            ]
        );
    }

}
