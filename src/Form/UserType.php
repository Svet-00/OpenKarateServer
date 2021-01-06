<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\AbstractType;
use App\Entity\UserLevel;
use App\Entity\User;

final class UserType extends AbstractType
{
  /**
   * @var string
   */
  private const LABEL = 'label';
  /**
   * @var string
   */
  private const REQUIRED = 'required';
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('email', EmailType::class)
      // ->add('agreeTerms', CheckboxType::class, [
      //   'mapped' => false,
      //   'constraints' => [
      //     new IsTrue([
      //       'message' => 'You should agree to our terms.',
      //     ]),
      //   ],
      // ])
      ->add('surname', TextType::class, [self::LABEL => 'Фамилия'])
      ->add('name', TextType::class, [self::LABEL => 'Имя'])
      ->add('patronymic', TextType::class, [
        self::LABEL => 'Отчество',
        self::REQUIRED => false
      ])
      ->add('birthday', BirthdayType::class, [
        'widget' => 'choice',
        'format' => 'dd.MM.yyyy',
        self::LABEL => 'Дата рождения'
      ])
      ->add('level', ChoiceType::class, [
        self::LABEL => 'Степень мастерства',
        'choices' => UserLevel::generateChoices()
      ]);

    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (
      FormEvent $event
    ): void {
      /** @var User $user */
      $user = $event->getData();
      $form = $event->getForm();

      if ($user->isTrainer()) {
        $form
          ->add('shortDescription', TextareaType::class, [
            self::LABEL => 'Короткое описание',
            'help' => 'Используется в приложении в списке тренеров',
            self::REQUIRED => false,
            'empty_data' => null
          ])
          ->add('longDescription', TextareaType::class, [
            self::LABEL => 'Полное описание',
            'help' => 'Используется в приложении на странице тренера',
            self::REQUIRED => false,
            'empty_data' => null
          ]);
      }
    });

    $builder->addEventListener(FormEvents::POST_SET_DATA, function (
      FormEvent $event
    ): void {
      $form = $event->getForm();
      $form->add('submit', SubmitType::class, [
        self::LABEL => 'Сохранить'
      ]);
    });
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class
    ]);
  }
}
