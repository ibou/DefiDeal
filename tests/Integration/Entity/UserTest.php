<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use App\Entity\User;
use App\Tests\Integration\ValidationTestCase;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserTest extends ValidationTestCase
{
    /**
     * Undocumented function.
     *
     * @return \Generator<
     *      string,
     *      array{entity: object, constraints: array<string, class-string>, groups: array<array-keys, string>}
     * >
     */
    public function provideEntities(): \Generator
    {
        yield 'empty email' => [
            'entity' => $this->createData(email: ''),
            'constraints' => ['email' => [NotBlank::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'empty invalid' => [
            'entity' => $this->createData(email: 'fail'),
            'constraints' => ['email' => [Email::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'exist email' => [
            'entity' => $this->createData(email: 'user+1@email.com'),
            'constraints' => ['email' => [UniqueEntity::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'empty nickname' => [
            'entity' => $this->createData(nickname: ''),
            'constraints' => ['nickname' => [NotBlank::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'exist nickname' => [
            'entity' => $this->createData(nickname: 'user+1'),
            'constraints' => ['nickname' => [UniqueEntity::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'empty plain Password' => [
            'entity' => $this->createData(plainPassword: ''),
            'constraints' => ['plainPassword' => [Length::class]],
            'groups' => ['Default',  'register'],
        ];
        yield 'plain password too short' => [
            'entity' => $this->createData(plainPassword: 'fail'),
            'constraints' => ['plainPassword' => [Length::class]],
            'groups' => ['Default',  'register'],
        ];
    }

    public function createData(
        string $email = 'user+11@email.com',
        string $nickname = 'user+11',
        string $plainPassword = 'password',
    ): User {
        $user = new User();
        $user->setNickname($nickname);
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);

        return $user;
    }
}
