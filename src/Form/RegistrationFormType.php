<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\AbstractType;
use App\Entity\UserLevel;
use App\Entity\User;

final class RegistrationFormType extends AbstractType
{
  /**
   * @var string
   */
  private const LABEL = 'label';
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
        'required' => false
      ])
      ->add('birthday', BirthdayType::class, [
        'widget' => 'choice',
        'format' => 'dd.MM.yyyy',
        self::LABEL => 'Дата рождения'
      ])
      ->add('level', ChoiceType::class, [
        self::LABEL => 'Степень мастерства',
        'choices' => UserLevel::generateChoices()
      ])
      ->add('plainPassword', PasswordType::class, [
        self::LABEL => 'Пароль',
        // instead of being set onto the object directly,
        // this is read and encoded in the controller
        'mapped' => false,
        'constraints' => [
          new NotBlank([
            'message' => 'Пожалуйста, введите пароль'
          ]),
          new Length([
            'min' => 6,
            'minMessage' =>
              'Пароль должен содержать минимум {{ limit }} символов',
            // max length allowed by Symfony for security reasons
            'max' => 4096
          ])
        ]
      ])
      ->add('repeatPassword', PasswordType::class, [
        self::LABEL => 'Повторите пароль',
        'mapped' => false,
        'constraints' => [
          new NotBlank([
            'message' => 'Пожалуйста, повторите пароль'
          ])
        ]
      ])
      ->add('submit', SubmitType::class, [
        self::LABEL => 'Создать аккаунт',
        'attr' => [
          'class' => 'btn btn-primary btn-block'
        ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class
    ]);
  }
}
