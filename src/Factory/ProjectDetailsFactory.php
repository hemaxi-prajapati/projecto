<?php

namespace App\Factory;

use App\Entity\ProjectDetails;
use App\Entity\User;
use App\Repository\ProjectDetailsRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ProjectDetails>
 *
 * @method        ProjectDetails|Proxy create(array|callable $attributes = [])
 * @method static ProjectDetails|Proxy createOne(array $attributes = [])
 * @method static ProjectDetails|Proxy find(object|array|mixed $criteria)
 * @method static ProjectDetails|Proxy findOrCreate(array $attributes)
 * @method static ProjectDetails|Proxy first(string $sortedField = 'id')
 * @method static ProjectDetails|Proxy last(string $sortedField = 'id')
 * @method static ProjectDetails|Proxy random(array $attributes = [])
 * @method static ProjectDetails|Proxy randomOrCreate(array $attributes = [])
 * @method static ProjectDetailsRepository|RepositoryProxy repository()
 * @method static ProjectDetails[]|Proxy[] all()
 * @method static ProjectDetails[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static ProjectDetails[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static ProjectDetails[]|Proxy[] findBy(array $attributes)
 * @method static ProjectDetails[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static ProjectDetails[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class ProjectDetailsFactory extends ModelFactory
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
            'Name' => self::faker()->name(),
            'Description' => self::faker()->text(100),
            'StartDate' => self::faker()->dateTime("now"),
            'EndDate' => self::faker()->dateTimeBetween('+1 week', '+1 month'),
            'Status' => self::faker()->randomElement([ProjectDetails::PROJECT_STATUS_IN_PROGRESS, ProjectDetails::PROJECT_STATUS_ON_HOLD, ProjectDetails::PROJECT_STATUS_OPEN, ProjectDetails::PROJECT_STATUS_COMPLETED]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ProjectDetails $projectDetails): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ProjectDetails::class;
    }
}
