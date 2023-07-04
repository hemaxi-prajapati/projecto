<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy create(array|callable $attributes = [])
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User|Proxy find(object|array|mixed $criteria)
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy first(string $sortedField = 'id')
 * @method static User|Proxy last(string $sortedField = 'id')
 * @method static User|Proxy random(array $attributes = [])
 * @method static User|Proxy randomOrCreate(array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method static User[]|Proxy[] all()
 * @method static User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[] findBy(array $attributes)
 * @method static User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */

    //  public function getTeamManeger(){}
    public function getRoleProjectManager(): self
    {
        return $this->addState(['roles' => [User::ROLE_PROJECT_MANAGER]]);
    }
    public function getRoleEmployee(): self
    {
        return $this->addState(['roles' => [User::ROLE_USER]]);
    }

    protected function getDefaults(): array
    {
        $roleArray = [User::ROLE_PROJECT_MANAGER, User::ROLE_USER];
        // $roleArray = [User::ROLE_PROJECT_MANAGER, User::ROLE_TEAM_MANAGER, User::ROLE_USER];
        return [
            'Firstname' => self::faker()->firstName(),
            'Lastname' => self::faker()->lastName(),
            'email' => self::faker()->email(),
            'plainPassword' => "123456",
            'createdAt' => self::faker()->dateTime(),
            'status' => User::USER_STATUS_ACTIIVE,
            'loginFrom' => User::USER_LOGIN_FROM_PROJECTO,
            'isVerified' => 1,
            // 'roles'=>[$roleArray[array_rand($roleArray)]],
            'roles' => [self::faker()->randomElement($roleArray)],
            'updatedAt' => self::faker()->dateTime(),
            'Department' => DepartmentFactory::random()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function (User $user) {
                if ($user->getPlainPassword()) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
                }
            });
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
