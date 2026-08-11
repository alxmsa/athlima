<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();
        
        $em->createQuery('DELETE FROM App\Entity\Utilisateur u WHERE u.email = :email')
        ->setParameter('email', 'newuser@athlima.fr')
        ->execute();
        
        static::ensureKernelShutdown();
    }
    /**
     * Test inscription avec données valides → 201
     */
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email'        => 'newuser@athlima.fr',
            'mot_de_passe' => 'Test1234!',
            'prenom'       => 'Alex',
            'nom'          => 'Test',
        ]));

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('newuser@athlima.fr', $data['email']);
    }

    /**
     * Test inscription sans email → 400
     */
    public function testRegisterMissingEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'mot_de_passe' => 'Test1234!',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test inscription sans mot de passe → 400
     */
    public function testRegisterMissingPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@athlima.fr',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test login sans token → 401
     */
    public function testProtectedRouteWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/profil');

        $this->assertResponseStatusCodeSame(401);
    }
}