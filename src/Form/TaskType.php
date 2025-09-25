<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'This field cannot be empty.',
                    ]),
                    new Length(
                        min:3,
                        max:255,
                        minMessage: 'The name is too short',
                        maxMessage: 'The name is too big',
                    )
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Length(
                        min:3,
                        max:255,
                        minMessage: 'The name is too short',
                        maxMessage: 'The name is too big',
                    )
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'New' => 1,
                    'In Progress' => 2,
                    'Completed' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'csrf_protection' => false
        ]);
    }

}
