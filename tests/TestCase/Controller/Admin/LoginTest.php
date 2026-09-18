<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class LoginTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    public function testAccessingAdminWithoutSessionRedirectsToLogin(): void
    {
        $this->get('/admin/businesses');

        $this->assertRedirectContains('/admin/users/login');
    }

    public function testLoginWithValidCredentialsEstablishesASession(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);
        $hasher = new DefaultPasswordHasher();
        $users->updateAll(['password' => $hasher->hash('correct-password')], ['id' => 1]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/login', [
            'email' => 'owner@alpha-hair-studio.test',
            'password' => 'correct-password',
        ]);

        $this->assertResponseSuccess();
        $this->assertSession(1, 'Auth.id');
    }

    public function testLoginWithInvalidCredentialsDoesNotEstablishASession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/login', [
            'email' => 'owner@alpha-hair-studio.test',
            'password' => 'wrong-password',
        ]);

        $this->assertResponseOk();
        $this->assertSessionNotHasKey('Auth.id');
    }
}
