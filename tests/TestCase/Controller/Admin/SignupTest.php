<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class SignupTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    public function testSignupCreatesABusinessAndItsOwner(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/signup', [
            'business_name' => 'Gamma Salon',
            'timezone' => 'Europe/London',
            'email' => 'owner@gamma-salon.test',
            'password' => 'super-secret-123',
        ]);

        $this->assertRedirectContains('/admin/users/login');

        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->find()->where(['slug' => 'gamma-salon'])->first();
        $this->assertNotNull($business);

        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId($business->id);
        $user = $users->find()->where(['email' => 'owner@gamma-salon.test'])->first();
        $this->assertNotNull($user);
        $this->assertSame('owner', $user->role);
        $this->assertNotSame('super-secret-123', $user->password);
    }

    public function testSignupThenLoginWorks(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/signup', [
            'business_name' => 'Delta Tutors',
            'timezone' => 'Europe/London',
            'email' => 'owner@delta-tutors.test',
            'password' => 'another-secret-456',
        ]);
        $this->assertRedirectContains('/admin/users/login');

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/login', [
            'email' => 'owner@delta-tutors.test',
            'password' => 'another-secret-456',
        ]);

        $this->assertResponseSuccess();
        $this->assertSessionHasKey('Auth.id');
    }

    public function testSignupWithAnEmailAlreadyInUseFailsValidationInsteadOf500ing(): void
    {
        // Fixture user id 1 already uses this email under a different
        // Business - signing up a second Business with the same email
        // must fail cleanly, not hit the database's own unique index.
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);
        $existing = $users->find('unscoped')->firstOrFail();

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/signup', [
            'business_name' => 'Epsilon Studio',
            'timezone' => 'Europe/London',
            'email' => $existing->email,
            'password' => 'super-secret-123',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('already in use');

        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $count = $businesses->find()->where(['slug' => 'epsilon-studio'])->count();
        $this->assertSame(0, $count);
    }

    public function testSignupWithMissingBusinessNameFailsValidation(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/admin/users/signup', [
            'business_name' => '',
            'timezone' => 'Europe/London',
            'email' => 'owner@no-name.test',
            'password' => 'super-secret-123',
        ]);

        $this->assertResponseOk();

        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $count = $businesses->find()->where(['name' => ''])->count();
        $this->assertSame(0, $count);
    }
}
