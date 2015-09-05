<?php namespace Anomaly\SearchModule\Index\Listener;

use Anomaly\SearchModule\Index\IndexManager;
use Anomaly\Streams\Platform\Addon\AddonCollection;
use Anomaly\Streams\Platform\Addon\Module\Module;
use Anomaly\Streams\Platform\Entry\Event\EntryWasSaved;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

/**
 * Class InsertItem
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\SearchModule\Index\Listener
 */
class InsertItem
{

    /**
     * The config repository.
     *
     * @var Repository
     */
    protected $config;

    /**
     * The addon collection.
     *
     * @var AddonCollection
     */
    protected $addons;

    /**
     * The index manager.
     *
     * @var IndexManager
     */
    protected $manager;

    /**
     * Create a new InsertItem instance.
     *
     * @param Repository      $config
     * @param AddonCollection $addons
     * @param IndexManager    $manager
     */
    public function __construct(Repository $config, AddonCollection $addons, IndexManager $manager)
    {
        $this->config  = $config;
        $this->addons  = $addons;
        $this->manager = $manager;
    }

    /**
     * Handle the event.
     *
     * @param EntryWasSaved $event
     */
    public function handle(EntryWasSaved $event)
    {
        $entry = $event->getEntry();

        /* @var Module $module */
        foreach ($this->addons->modules() as $module) {

            $key = $module->getNamespace('search.' . get_class($entry));

            foreach ($this->config->get($key, []) as $index => $config) {
                $this->manager
                    ->setIndex($index)
                    ->setReference($entry)
                    ->setExtra(array_get($config, 'extra', []))
                    ->setFields(array_get($config, 'fields', []))
                    ->setEnabled(array_get($config, 'enabled', true))
                    ->insert();
            }
        }
    }
}