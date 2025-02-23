<?php

namespace App\Form;

use App\Entity\Conversation;
use App\Entity\Messages;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', Textareatype::class, [
                'attr' => ['placeholder' => 'Type your message..']

            // ->add('sending_date', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('author', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('conversation', EntityType::class, [
            //     'class' => Conversation::class,
            //     'choice_label' => 'id',
            // ])
         ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Messages::class,
        ]);
    }
}
