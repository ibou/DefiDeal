<?php

namespace App\Tests\Functional\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector;

class AuthenticationTest extends WebTestCase
{
    public function testShouldAuthenticate(): void
    {
    // test authentication

        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/login');
        $client->enableProfiler();
        $this->assertResponseIsSuccessful();
        $client->submitForm('Log in', [
            '_username' => 'user+1@email.com',
            '_password' => 'password',
        ]);
        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        
        if (($profile = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profile->getCollector('security');

            self::assertTrue($securityCollector->isAuthenticated());
        }
    }

    /**
     * @param array{_username: string, _password: string} $formData
     *
     * @test
     *
     * @dataProvider provideInvalidData
     */
    public function shouldNotAuthenticateWithBadCredentials(array $formData): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();

        $client->enableProfiler();

        $client->submitForm('Log in', $formData);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        if (($profile = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profile->getCollector('security');
            self::assertFalse($securityCollector->isAuthenticated());
        }
    }

    /**
     * @return \Generator{_username: string, _password: string}
     */
    public function provideInvalidData(): \Generator
    {
        $baseData = static fn (array $data): array => $data + [
            '_username' => 'user+1@email.com',
            '_password' => 'password',
        ];

        yield 'wrong email' => [$baseData(['_username' => 'fail@email.com'])];
        yield 'empty email' => [$baseData(['_username' => ''])];
        yield 'wrong password' => [$baseData(['_password' => 'fail'])];
        yield 'empty password' => [$baseData(['_password' => ''])];
        yield 'empty csrf' => [$baseData(['_csrf_token' => ''])];
        yield 'wrong csrf' => [$baseData(['_csrf_token' => 'fail'])];
    }
}
