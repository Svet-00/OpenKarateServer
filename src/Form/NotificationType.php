<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use App\Notifications\Notification;

final class NotificationType extends AbstractType
{
  /**
   * @var string
   */
  private const LABEL = 'label';
  public function buildForm(FormBuilderInterface $formBuilder, array $options): void
  {
    $formBuilder
      ->add('title', TextType::class, [self::LABEL => 'Заголовок'])
      ->add('body', TextareaType::class, [
        self::LABEL => 'Текст',
        'required' => false,
        'help' =>
          'Если вы добавите изображение, на некоторых устройствах текст может не отобразиться.'
      ])
      ->add('image', FileType::class, [
        self::LABEL => 'Изображение',
        'required' => false,
        'attr' => ['accept' => 'image/png,image/jpeg'],
        'help' => 'Размер изображения не должен превышать 1 мегабайта',
        'constraints' => [
          new File([
            'maxSize' => '1M',
            'maxSizeMessage' =>
              'Размер файла изображения слишком большой ({{ size }}{{ suffix }}). ' .
              'Максимальный допустимый размер {{ limit }}{{ suffix }}.'
          ])
        ]
      ])
      ->add('submit', SubmitType::class, [self::LABEL => 'Отправить']);
  }

  public function configureOptions(OptionsResolver $optionsResolver): void
  {
    $optionsResolver->setDefaults([
      'data_class' => Notification::class
    ]);
  }
}
