<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use App\Form\LinkType;
use App\Entity\Event;

final class EventType extends AbstractType
{
  /**
   * @var string
   */
  private const LABEL = 'label';
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', TextType::class, [self::LABEL => 'Заголовок'])
      ->add('description', TextareaType::class, [
        self::LABEL => 'Описание',
        'required' => false
      ])
      ->add('address', TextType::class, [self::LABEL => 'Адрес проведения'])
      ->add('startDate', DateType::class, [
        self::LABEL => 'Дата начала',
        'widget' => 'choice',
        'format' => 'dd.MM.yyyy'
      ])
      ->add('endDate', DateType::class, [
        self::LABEL => 'Дата окончания',
        'widget' => 'choice',
        'format' => 'dd.MM.yyyy'
      ])
      ->add('type', TextType::class, [
        self::LABEL => 'Тип события',
        'trim' => true,
        'attr' => ['placeholder' => 'Например, "Ката"']
      ])
      ->add('level', TextType::class, [
        self::LABEL => 'Уровень соревнований',
        'trim' => true,
        'attr' => ['placeholder' => 'Например, "Чемпионат города"']
      ])
      ->add('links', CollectionType::class, [
        self::LABEL => false,
        'prototype' => true,
        'allow_add' => true,
        'allow_delete' => true,
        'entry_type' => LinkType::class,
        'entry_options' => [
          self::LABEL => false,
          'row_attr' => ['class' => 'm-0']
        ]
      ])
      ->add('documents', FileType::class, [
        'mapped' => false,
        'required' => false,
        self::LABEL => 'Документы',
        'multiple' => true,
        'help' => 'Можно выбрать и загрузить несколько документов за раз.'
      ])
      ->add('submit', SubmitType::class, [
        self::LABEL => 'Опубликовать',
        'row_attr' => ['class' => 'm-0']
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Event::class,
      'post_max_size_message' =>
        'Размер отправленных файлов превышает допустимый предел в ' .
        \ini_get('post_max_size')
    ]);
  }
}
