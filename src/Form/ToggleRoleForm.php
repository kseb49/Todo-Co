<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

class ToggleRoleForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add(
                'roles',
                CheckboxType::class,
                [
                    'label' => 'Rôle Administrateur ?',
                    'help' => 'Cocher la case pour accorder les droits administrateurs à cette utilisateur',
                    'required' => false,
                    'mapped' => false,
                    "value" => true,
                ]
            );

    }

    #[CodeCoverageIgnore]
    public function concompteOptions(OptionsResolver $resolver) :void
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
