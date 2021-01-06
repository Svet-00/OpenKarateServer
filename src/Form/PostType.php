<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use App\Entity\Post;

final class PostType extends AbstractType
{
  /**
   * @var string
   */
  private const LABEL = 'label';
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('text', TextareaType::class, [self::LABEL => 'Текст новости'])
      ->add('photo', FileType::class, [
        'mapped' => false,
        'required' => false,
        self::LABEL => 'Изображение',
        'attr' => ['accept' => 'image/jpeg,image/png']
      ])
      ->add('documents', FileType::class, [
        'mapped' => false,
        'required' => false,
        self::LABEL => 'Документы',
        'multiple' => true,
        'help' => 'Можно выбрать и загрузить несколько документов за раз.'
      ])
      ->add('submit', SubmitType::class, [self::LABEL => 'Опубликовать']);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Post::class,
      'post_max_size_message' =>
        'Размер отправленных файлов превышает допустимый предел в ' .
        \ini_get('post_max_size')
    ]);
  }
}
