<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])

            ->add('email', EmailType::class, ['label' => 'Adresse email'])

            ->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                    'required' => true,
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
                ]
                );
            // ->add('roles', CheckboxType::class,
            //     [
            //         'label' => 'Rôle Administrateur ?',
            //         'help' => 'Cocher la case pour accorder les droits administrateurs à cette utilisateur',
            //         'required' => false,
            //         'value' => true,
            //         'mapped' => false,
            //     ]
            // );

    }


    public function configureOptions(OptionsResolver $resolver) :void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                // Comment me to reactivate the html5 validation!.
                'attr' => ['novalidate' => 'novalidate'],
            ]
        );

    }
}
