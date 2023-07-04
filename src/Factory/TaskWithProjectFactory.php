<?php

namespace App\Factory;

use App\Entity\ProjectDetails;
use App\Entity\TaskWithProject;
use App\Entity\User;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<TaskWithProject>
 *
 * @method        TaskWithProject|Proxy create(array|callable $attributes = [])
 * @method static TaskWithProject|Proxy createOne(array $attributes = [])
 * @method static TaskWithProject|Proxy find(object|array|mixed $criteria)
 * @method static TaskWithProject|Proxy findOrCreate(array $attributes)
 * @method static TaskWithProject|Proxy first(string $sortedField = 'id')
 * @method static TaskWithProject|Proxy last(string $sortedField = 'id')
 * @method static TaskWithProject|Proxy random(array $attributes = [])
 * @method static TaskWithProject|Proxy randomOrCreate(array $attributes = [])
 * @method static TaskWithProjectRepository|RepositoryProxy repository()
 * @method static TaskWithProject[]|Proxy[] all()
 * @method static TaskWithProject[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static TaskWithProject[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static TaskWithProject[]|Proxy[] findBy(array $attributes)
 * @method static TaskWithProject[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static TaskWithProject[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class TaskWithProjectFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private UserRepository $userRepository)
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
            'ActualEndDate' => self::faker()->dateTime(),
            'ActualStartDate' => self::faker()->dateTime(),
            'Description' => self::faker()->text(30),
            'Priority' => self::faker()->randomElement([TaskWithProject::TASK_PRIORITY_HIGH,TaskWithProject::TASK_PRIORITY_LOW,TaskWithProject::TASK_PRIORITY_MEDIUM]),
            'Status' => self::faker()->randomElement([TaskWithProject::TASK_STATUS_COMPLETED,TaskWithProject::TASK_STATUS_IN_PROGRESS,TaskWithProject::TASK_STATUS_ON_HOLD,TaskWithProject::TASK_STATUS_OPEN]),
            'Title' => self::faker()->word(),
           
            'project' => ProjectDetailsFactory::random(),
    
            'addUser' => UserFactory::random([]),
            'progress' => self::faker()->randomDigit()

        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(TaskWithProject $taskWithProject): void {})
        ;
    }

    protected static function getClass(): string
    {
        return TaskWithProject::class;
    }
}