<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Form;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['required' => true])
            ->add('realName', TextType::class, ['required' => true])
            ->add('email', EmailType::class, ['required' => true])
            ->add('plainPassword', TextType::class, ['label' => 'Password', 'required' => false])
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'choices'  => [
                        'Admin' => 'ROLE_ADMIN',
                        'User'  => 'ROLE_USER',
                    ]
                ]
            )
            ->add('githubId', TextType::class, ['required' => false])
            ->add('googleId', TextType::class, ['required' => false])
            ->add('facebookId', TextType::class, ['required' => false])
            ->add('enabled', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class
            ]
        );
    }

}
