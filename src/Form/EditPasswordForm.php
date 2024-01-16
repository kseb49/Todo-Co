<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

class EditPasswordForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add(
                'password',
                RepeatedType::class,
                [
                 'type' => PasswordType::class,
                 'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                 'required' => true,
                 'first_options'  => ['label' => 'Mot de passe'],
                 'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
                ]
            );

    }


    #[CodeCoverageIgnore]
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
