<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ValidationTestCase extends KernelTestCase
{
    /**
     * @param array<string, array<array-key, class-string>> $constraints
     * @param array<array-keys, string>                     $groups
     * @dataProvider provideEntities
     */
    public function test(object $entity, array $constraints = [], array $groups = []): void
    {
        self::bootKernel();

        // $validator = self::getContainer()->get(ValidatorInterface::class);

        $container = static::getContainer();

        /** @var ValidatorInterface $validator */
        $validator = $container->get(ValidatorInterface::class);

        $constraintViolationList = $validator->validate(value: $entity, groups: $groups);
        self::assertCount(count($constraints), $constraintViolationList);

        /** @var array<string, array<array-key, class-string<Constraints>>> */
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            if (!isset($errors[$violation->getPropertyPath()])) {
                $errors[$violation->getPropertyPath()] = [];
            }
            /** @var Constraint $constraint*/
            if (($constraint = $violation->getConstraint()) !== null) {
                $errors[$violation->getPropertyPath()][] = $constraint::class;
            }
        }

        self::assertEquals($constraints, $errors);
    }

    /**
     * @return \Generator<
     *      string,
     *      array{
     *          entity: object,
     *          constraints: array<string, array<array-key, class-string>>,
     *          groups: array<array-keys, string>}
     * >
     */
    abstract public function provideEntities(): \Generator;
}
