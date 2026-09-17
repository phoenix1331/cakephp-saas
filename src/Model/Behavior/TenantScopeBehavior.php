<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;
use RuntimeException;

/**
 * Restricts finds and saves on the attached Table to a single tenant
 * (Business), the Table-level equivalent of a Laravel Eloquent global scope.
 *
 * The tenant id is set explicitly via setTenantId() rather than read from a
 * global/session, which keeps the behavior testable in isolation and makes
 * every tenant-scoped query traceable to a concrete id.
 */
class TenantScopeBehavior extends Behavior
{
    protected array $_defaultConfig = [
        'field' => 'business_id',
    ];

    protected ?int $tenantId = null;

    /**
     * Sets the current tenant id that all finds and saves will be scoped to.
     *
     * @param int|null $tenantId The Business id to scope to, or null to clear it.
     * @return void
     */
    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Returns the current tenant id, or null if none has been set.
     *
     * @return int|null
     */
    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    /**
     * Adds a WHERE condition restricting the query to the current tenant.
     *
     * @param \Cake\Event\EventInterface $event The beforeFind event.
     * @param \Cake\ORM\Query\SelectQuery $query The query to scope.
     * @param \ArrayObject $options Find options.
     * @param bool $primary Whether this is the primary query.
     * @return void
     */
    public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void
    {
        if ($this->tenantId === null) {
            throw new RuntimeException(sprintf(
                'TenantScopeBehavior on %s requires setTenantId() to be called before querying.',
                $this->table()->getAlias(),
            ));
        }

        $field = $this->table()->aliasField($this->getConfig('field'));
        $query->andWhere([$field => $this->tenantId]);
    }

    /**
     * Stamps the tenant field on new entities and refuses to save an entity
     * whose tenant field does not match the current tenant.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event.
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved.
     * @param \ArrayObject $options Save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($this->tenantId === null) {
            throw new RuntimeException(sprintf(
                'TenantScopeBehavior on %s requires setTenantId() to be called before saving.',
                $this->table()->getAlias(),
            ));
        }

        $field = $this->getConfig('field');

        if ($entity->isNew() && $entity->get($field) === null) {
            $entity->set($field, $this->tenantId);
        }

        if ((int)$entity->get($field) !== $this->tenantId) {
            throw new RuntimeException(sprintf(
                'Refusing to save %s entity with %s = %s outside the current tenant (%s).',
                $this->table()->getAlias(),
                $field,
                (string)$entity->get($field),
                (string)$this->tenantId,
            ));
        }
    }
}
