<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add(
                'title',
                options:['label' => 'Titre'],
            )
            ->add(
                'content',
                TextareaType::class,
                ['label' => 'Contenu']
            )
            ->add(
                'user',
                EntityType::class,
                [
                 'class' => User::class,
                 'choice_label' => 'username',
                 'multiple' => true,
                 'attr' => ['class' => 'form-control'],
                 'label' => 'Mentionné des utilisateurs',
                 'help' => 'Vous pouvez mentionné un ou plusieurs utilisateurs qui pourront consulter la tâche',
                 'required' => false,
                ]
            );

    }


    #[CodeCoverageIgnore]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Task::class,
                // Comment me to reactivate the html5 validation!.
                'attr' => ['novalidate' => 'novalidate'],
            ]
        );

    }


}
