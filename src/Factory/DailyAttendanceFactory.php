<?php

namespace App\Factory;

use App\Entity\DailyAttendance;
use App\Repository\DailyAttendanceRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<DailyAttendance>
 *
 * @method        DailyAttendance|Proxy                     create(array|callable $attributes = [])
 * @method static DailyAttendance|Proxy                     createOne(array $attributes = [])
 * @method static DailyAttendance|Proxy                     find(object|array|mixed $criteria)
 * @method static DailyAttendance|Proxy                     findOrCreate(array $attributes)
 * @method static DailyAttendance|Proxy                     first(string $sortedField = 'id')
 * @method static DailyAttendance|Proxy                     last(string $sortedField = 'id')
 * @method static DailyAttendance|Proxy                     random(array $attributes = [])
 * @method static DailyAttendance|Proxy                     randomOrCreate(array $attributes = [])
 * @method static DailyAttendanceRepository|RepositoryProxy repository()
 * @method static DailyAttendance[]|Proxy[]                 all()
 * @method static DailyAttendance[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static DailyAttendance[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static DailyAttendance[]|Proxy[]                 findBy(array $attributes)
 * @method static DailyAttendance[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static DailyAttendance[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class DailyAttendanceFactory extends ModelFactory
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
        $startTime = self::faker()->dateTime();
        $EndTime = self::faker()->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s') . ' +1 hour');
        return [
            
            'checkIn' => $startTime,
            'checkOut' => $EndTime,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(DailyAttendance $dailyAttendance): void {})
        ;
    }

    protected static function getClass(): string
    {
        return DailyAttendance::class;
    }
}
