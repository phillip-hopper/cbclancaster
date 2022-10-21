<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Symlink
 *
 * @copyright   (C) 2022 Phillip Hopper
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * FileSystem Local plugin.
 *
 * The plugin to deal with the local filesystem in Media Manager.
 *
 * @since  0.0.1
 */
class PlgFileSystemSymlink extends CMSPlugin implements ProviderInterface
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  0.0.1
     */
    protected $autoloadLanguage = true;

    /**
     * Setup Providers for Local Adapter
     *
     * @param   MediaProviderEvent  $event  Event for ProviderManager
     *
     * @return   void
     *
     * @since    0.0.1
     */
    public function onSetupProviders(MediaProviderEvent $event)
    {
        $event->getProviderManager()->registerProvider($this);
    }

    /**
     * Returns the ID of the provider
     *
     * @return  string
     *
     * @since  0.0.1
     */
    public function getID(): string
    {
        return $this->_name;
    }

    /**
     * Returns the display name of the provider
     *
     * @return string
     *
     * @since  0.0.1
     */
    public function getDisplayName(): string
    {
        return Text::_('PLG_FILESYSTEM_SYMLINK_DEFAULT_NAME');
    }

    /**
     * Returns and array of adapters
     *
     * @return  AdapterInterface[]
     *
     * @since  0.0.1
     */
    public function getAdapters(): array
    {
        $adapters = [];
        $directories = $this->params->get('directories', '[{"directory": "images"}]');

        // Do a check if default settings are not saved by user
        // If not initialize them manually
        if (is_string($directories)) {
            $directories = json_decode($directories);
        }

        foreach ($directories as $directoryEntity) {
            if ($directoryEntity->directory) {
                $directoryPath = JPATH_ROOT . '/' . $directoryEntity->directory;
                $directoryPath = rtrim($directoryPath) . '/';

	            /** @noinspection PhpFullyQualifiedNameUsageInspection */
	            $adapter = new \Joomla\Plugin\Filesystem\Symlink\Adapter\SymlinkAdapter(
                    $directoryPath,
                    $directoryEntity->directory
                );

                $adapters[$adapter->getAdapterName()] = $adapter;
            }
        }

        return $adapters;
    }
}
