<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

class TaskForm extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options) :void
    {
        $builder
            ->add('title')
            ->add(
                'content',
                TextareaType::class,
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
