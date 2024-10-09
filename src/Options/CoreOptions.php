<?php

declare(strict_types=1);

namespace Lmc\User\Core\Options;

use InvalidArgumentException;
use Laminas\Stdlib\AbstractOptions;
use Lmc\User\Core\Entity\User;
use Lmc\User\Core\Entity\UserInterface;
use Webmozart\Assert\Assert;

use function array_is_list;
use function is_array;

/**
 * @template TValue
 */
class CoreOptions extends AbstractOptions
{
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable


    protected string $userEntityClass = User::class;

    protected string $tableName = 'user';

    protected array $authAdapters = [];

     /**
      * set user entity class name
      */
    public function setUserEntityClass(string $userEntityClass): CoreOptions
    {
        Assert::classExists($userEntityClass);
        Assert::implementsInterface($userEntityClass, UserInterface::class);
        $this->userEntityClass = $userEntityClass;
        return $this;
    }

    /**
     * get user entity class name
     */
    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    /**
     * set user table name
     */
    public function setTableName(string $tableName): CoreOptions
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * get user table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setAuthAdapters(array $authAdaptersConfig): CoreOptions
    {
        if (array_is_list($authAdaptersConfig)) {
            throw new InvalidArgumentException('Authentication adapter configuration cannot be a list array');
        }

        /**
         * @var ?int $priority
         * @var  string|array $authAdapterConfigOrName
         */
        foreach ($authAdaptersConfig as $priority => $authAdapterConfigOrName) {
            if (! is_int($priority)) {
                throw new InvalidArgumentException('Authentication adapter priority is not an integer');
            }
            $authAdapterConfig = [];
            if (! is_array($authAdapterConfigOrName)) {
                $authAdapterConfigOrName = ['name' => $authAdapterConfigOrName];
            }
            if (! isset($authAdapterConfigOrName['name'])) {
                throw new InvalidArgumentException('Authentication adapter configuration key "name" is missing');
            }
            $authAdapterConfig['name']     = $authAdapterConfigOrName['name'];
            $authAdapterConfig['priority'] = $priority ?? ChainableAdapterConfig::DEFAULT_PRIORITY;
            $authAdapterConfig['options']  = $authAdapterConfigOrName['options'] ?? [];
            $this->authAdapters[]          = new ChainableAdapterConfig($authAdapterConfig);
        }
        return $this;
    }

    public function getAuthAdapters(): array
    {
        return $this->authAdapters;
    }

    public function findAuthAdapterByName(string $name): ?ChainableAdapterConfig
    {
        /** @var ChainableAdapterConfig $authAdapterConfig */
        foreach ($this->authAdapters as $authAdapterConfig) {
            if ($authAdapterConfig->getName() === $name) {
                return $authAdapterConfig;
            }
        }
        return null;
    }
}
