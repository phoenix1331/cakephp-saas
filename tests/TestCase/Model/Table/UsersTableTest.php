<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Covers UsersTable::isWithinStaffLimit() - the plan-limit gate ("Solo: 1
 * staff member; Team: up to 5" per the brief), fired as a buildRules()
 * create-only rule so it applies to every code path that creates a User
 * (signup, Admin\UsersController::add()), not just one controller.
 */
class UsersTableTest extends TestCase
{
    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Plans',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    private function newUser(int $businessId, string $email): User
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId($businessId);

        return $users->newEntity([
            'business_id' => $businessId,
            'email' => $email,
            'password' => 'password123',
            'role' => 'staff',
        ]);
    }

    public function testRejectsANewUserWhenTheBusinessIsAtItsPlansStaffLimit(): void
    {
        // Business 2 has Plan 1 (Solo, staff_limit 1) and already has one
        // User (fixture id 2), so a second is one over the limit.
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(2);
        $businesses->patchEntity($business, ['plan_id' => 1]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $this->newUser(2, 'second@beta-tutoring.test');

        $saved = $users->save($user);

        $this->assertFalse($saved);
        $this->assertArrayHasKey('business_id', $user->getErrors());
    }

    public function testAllowsANewUserWhenUnderThePlansStaffLimit(): void
    {
        // Business 2 has Plan 2 (Team, staff_limit 5) and one existing User.
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(2);
        $businesses->patchEntity($business, ['plan_id' => 2]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $this->newUser(2, 'second@beta-tutoring.test');

        $saved = $users->save($user);

        $this->assertNotFalse($saved);
    }

    public function testUsesTheCheapestPlansLimitWhenNoPlanIsChosenYet(): void
    {
        // Business 2 has plan_id null (still on trial) and one existing
        // User. The cheapest Plan (Solo, staff_limit 1) applies, so a
        // second User is one over the limit.
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $this->newUser(2, 'second@beta-tutoring.test');

        $saved = $users->save($user);

        $this->assertFalse($saved);
        $this->assertArrayHasKey('business_id', $user->getErrors());
    }

    public function testDoesNotCountUsersFromAnotherBusinessTowardTheLimit(): void
    {
        // Business 1 already has 2 Users (fixture ids 1 and 3) - a rule
        // that failed to scope by business_id could over- or under-count
        // by including business 2's User too.
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['plan_id' => 2]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $this->newUser(1, 'third@alpha-hair-studio.test');

        $saved = $users->save($user);

        $this->assertNotFalse($saved);
    }

    public function testRejectsADuplicateEmailAcrossDifferentBusinesses(): void
    {
        // Fixture user id 1 (alpha-hair-studio, business 1) already uses
        // this email - a second Business signing up a User with the same
        // email must be rejected too, even though isEmailUnique() has to
        // look past TenantScopeBehavior's own scoping to see it.
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);
        $existing = $users->find('unscoped')->where(['business_id' => 1])->firstOrFail();

        $user = $this->newUser(2, $existing->email);

        $saved = $users->save($user);

        $this->assertFalse($saved);
        $this->assertArrayHasKey('email', $user->getErrors());
    }

    public function testEditingAnExistingUserIsNeverBlockedByTheLimit(): void
    {
        // addCreate() only - the rule must not fire on update, or a
        // Business already over its limit (e.g. after downgrading plans)
        // could never edit its existing staff again.
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(2);
        $businesses->patchEntity($business, ['plan_id' => 1]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(2);
        $user = $users->find('unscoped')->where(['id' => 2])->firstOrFail();
        $user = $users->patchEntity($user, ['email' => 'renamed@beta-tutoring.test']);

        $saved = $users->save($user);

        $this->assertNotFalse($saved);
    }
}
