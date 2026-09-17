<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Plans Model
 *
 * @property \App\Model\Table\BusinessesTable&\Cake\ORM\Association\HasMany $Businesses
 * @method \App\Model\Entity\Plan newEmptyEntity()
 * @method \App\Model\Entity\Plan newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Plan> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Plan get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Plan findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Plan patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Plan> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Plan|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Plan saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Plan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Plan>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Plan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Plan> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Plan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Plan>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Plan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Plan> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PlansTable extends Table
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

        $this->setTable('plans');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Businesses', [
            'foreignKey' => 'plan_id',
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
            ->integer('staff_limit')
            ->requirePresence('staff_limit', 'create')
            ->notEmptyString('staff_limit')
            ->greaterThan('staff_limit', 0);

        $validator
            ->scalar('stripe_price_id')
            ->maxLength('stripe_price_id', 255)
            ->allowEmptyString('stripe_price_id');

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->notEmptyString('price')
            ->greaterThanOrEqual('price', 0);

        return $validator;
    }
}
