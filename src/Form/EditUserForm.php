<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class EditUserForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                 'label' => "Nom d'utilisateur",
                 'required' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                 'label' => 'Adresse email',
                 'required' => false,
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
