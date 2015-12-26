<?php

namespace RestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostRestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array(
                'label' => 'label.title',
            ))
            ->add('summary', null, array(
                'label' => 'label.summary',
            ))
            ->add('content', null, array(
                'label' => 'label.content'
            ))
            ->add('authorEmail', null, array(
                'label' => 'label.author_email',
                'constraints' => array(
                    new Email(),
                    new NotBlank(),
                )
            ))
            ->add('publishedAt', 'AppBundle\Form\Type\DateTimePickerType', array(
                'label' => 'label.published_at'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Post',
            'csrf_protection' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'post';
    }
}
