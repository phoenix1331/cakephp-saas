<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Businesses Model
 *
 * @method \App\Model\Entity\Business newEmptyEntity()
 * @method \App\Model\Entity\Business newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Business> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Business get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Business findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Business patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Business> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Business|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Business saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Business>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Business>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Business>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Business> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Business>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Business>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Business>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Business> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BusinessesTable extends Table
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

        $this->setTable('businesses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'business_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('timezone')
            ->maxLength('timezone', 255)
            ->requirePresence('timezone', 'create')
            ->notEmptyString('timezone');

        $validator
            ->scalar('stripe_customer_id')
            ->maxLength('stripe_customer_id', 255)
            ->allowEmptyString('stripe_customer_id');

        $validator
            ->scalar('subscription_status')
            ->maxLength('subscription_status', 255)
            ->allowEmptyString('subscription_status');

        $validator
            ->dateTime('trial_ends_at')
            ->allowEmptyDateTime('trial_ends_at');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
