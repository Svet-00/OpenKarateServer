<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\PropertyTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\CompleteVarDocTypePropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\Set\ValueObject\SetList;
use Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector;
use Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Core\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
  // get parameters
  $parameters = $containerConfigurator->parameters();

  $parameters->set(
    Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
    __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
  );

  // paths to refactor; solid alternative to CLI arguments
  $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

  // $parameters->set(Option::SKIP, [__DIR__ . '/src/Migrations/']);
  $parameters->set(Option::EXCLUDE_PATHS, [__DIR__ . '/src/Migrations/']);

  // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
  // $parameters->set(Option::AUTOLOAD_PATHS, []);

  // is your PHP version different from the one your refactor to? [default: your PHP version], uses PHP_VERSION_ID format
  // $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);
  $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');

  // auto import fully qualified class names? [default: false]
  $parameters->set(Option::AUTO_IMPORT_NAMES, false);

  // skip root namespace classes, like \DateTime or \Exception [default: true]
  $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

  // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
  $parameters->set(Option::IMPORT_DOC_BLOCKS, true);

  // Run Rector only on changed files
  $parameters->set(Option::ENABLE_CACHE, true);

  // get services (needed for register a single rule)
  $services = $containerConfigurator->services();

  // register a single rule
  $services->set(ChangeAndIfToEarlyReturnRector::class);
  $services->set(ChangeIfElseValueAssignToEarlyReturnRector::class);
  $services->set(ChangeNestedForeachIfsToEarlyContinueRector::class);
  $services->set(ChangeNestedIfsToEarlyReturnRector::class);
  $services->set(RemoveAlwaysElseRector::class);
  $services->set(CountArrayToEmptyArrayComparisonRector::class);
  $services->set(PreslashSimpleFunctionRector::class);
  $services->set(AddArrayParamDocTypeRector::class);
  $services->set(AddArrayReturnDocTypeRector::class);
  $services->set(AddClosureReturnTypeRector::class);
  $services->set(CompleteVarDocTypePropertyRector::class);
  $services->set(PropertyTypeDeclarationRector::class);
  $services->set(ReturnTypeDeclarationRector::class);
};
