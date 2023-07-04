<?php

namespace App\Factory;

use App\Entity\ProjectAssignment;
use App\Repository\ProjectAssignmentRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProjectAssignment>
 *
 * @method        ProjectAssignment|Proxy create(array|callable $attributes = [])
 * @method static ProjectAssignment|Proxy createOne(array $attributes = [])
 * @method static ProjectAssignment|Proxy find(object|array|mixed $criteria)
 * @method static ProjectAssignment|Proxy findOrCreate(array $attributes)
 * @method static ProjectAssignment|Proxy first(string $sortedField = 'id')
 * @method static ProjectAssignment|Proxy last(string $sortedField = 'id')
 * @method static ProjectAssignment|Proxy random(array $attributes = [])
 * @method static ProjectAssignment|Proxy randomOrCreate(array $attributes = [])
 * @method static ProjectAssignmentRepository|RepositoryProxy repository()
 * @method static ProjectAssignment[]|Proxy[] all()
 * @method static ProjectAssignment[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ProjectAssignment[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static ProjectAssignment[]|Proxy[] findBy(array $attributes)
 * @method static ProjectAssignment[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static ProjectAssignment[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class ProjectAssignmentFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'AssignAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'Status' => self::faker()->randomElement([ProjectAssignment::USER_TASK_STATUS_APPROVED,ProjectAssignment::USER_TASK_STATUS_REJECTED,ProjectAssignment::USER_TASK_STATUS_YET_TO_ASSIGN]),
            'createdAt' => self::faker()->dateTime(),
            'project' => ProjectDetailsFactory::random(),
            'updatedAt' => self::faker()->dateTime(),
            'user' => UserFactory::random(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ProjectAssignment $projectAssignment): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ProjectAssignment::class;
    }
}
