<?php

namespace App\Tests\Functional\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector;

class RegistrationTest extends WebTestCase
{
    public function testShouldRegister(): void
    { 

        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        // $client->enableProfiler();
        $this->assertResponseIsSuccessful();
        // $client->submitForm('Register', [
        //     '_username' => 'user+10@email.com',
        //     '_password' => 'password',
        // ]);
        
        //$this->assertResponseStatusCodeSame(Response::HTTP_FOUND); 
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
    }
}
