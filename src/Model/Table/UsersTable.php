<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\BusinessesTable&\Cake\ORM\Association\BelongsTo $Businesses
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('TenantScope.TenantScope');

        $this->belongsTo('Businesses', [
            'foreignKey' => 'business_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsToMany('Services', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'service_id',
            'joinTable' => 'services_users',
        ]);
        $this->hasMany('Availabilities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Bookings', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('business_id')
            ->notEmptyString('business_id');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('role')
            ->maxLength('role', 255)
            ->requirePresence('role', 'create')
            ->notEmptyString('role')
            ->inList('role', ['owner', 'staff']);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->existsIn(['business_id'], 'Businesses'), ['errorField' => 'business_id']);
        $rules->addCreate([$this, 'isWithinStaffLimit'], 'isWithinStaffLimit', [
            'errorField' => 'business_id',
            'message' => __('This business has reached its staff limit for its current plan. Upgrade to add more.'),
        ]);

        return $rules;
    }

    /**
     * Blocks creating a new User once a Business is at its Plan's staff
     * limit - "Solo: 1 staff member; Team: up to 5" per the brief. Counts
     * every User on the Business (owner included), since a Solo business's
     * one allowed User is the owner themself acting as staff, not the
     * owner plus one separate staff member.
     *
     * A Business with no Plan chosen yet (still on trial) is capped at the
     * cheapest Plan's limit instead of being unlimited - see
     * PlansTable::getCheapest().
     *
     * @param \Cake\Datasource\EntityInterface $entity The User being created.
     * @param array<string, mixed> $options Rule options (unused).
     * @return bool
     */
    public function isWithinStaffLimit(EntityInterface $entity, array $options = []): bool
    {
        /** @var \App\Model\Entity\User $entity */
        $business = $this->Businesses->get($entity->business_id, contain: ['Plans']);

        $plan = $business->plan ?? TableRegistry::getTableLocator()->get('Plans')->getCheapest();
        if ($plan === null) {
            // No Plan exists at all yet - nothing to enforce against.
            return true;
        }

        $currentCount = $this->find('unscoped')
            ->where(['business_id' => $business->id])
            ->count();

        return $currentCount < $plan->staff_limit;
    }
}
