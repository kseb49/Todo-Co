<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Application Tests class
 */
class HomeControllerTest extends WebTestCase
{

    private KernelBrowser|null $client = null;

    private UserRepository|null $userRepository = null;

    /**
     * user with ROLE_USER
     *
     * @var User|null
     */
    private User|null $user = null;


    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->user = $this->userRepository->findOneByEmail('testuser1@test.com');

    }


    public function testHomePage()
    {
        $crawler = $this->client->request('GET', '/');
        $link = $crawler->filter('a[href="/login"]')->text();
        $this->assertResponseIsSuccessful();
        $this->assertSame('Se connecter', $link);
        $this->client->clickLink('Se connecter');
        $this->assertPageTitleContains('Log in!');
        $this->assertSelectorExists('form');

    }


    public function testHomePageRedirect()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/');
        $link = $crawler->filter('a[href="/logout"]')->text();
        $this->assertResponseIsSuccessful();
        $this->assertSame('Se déconnecter', $link);

    }


}
