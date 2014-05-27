<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Description of Installer
 *
 * @author serginho
 */
class ComposerInstaller extends LibraryInstaller{
	
    public function supports($packageType)
    {
        return $packageType === 'door-module';
    }	
	
    /**
     * Retrieves the Installer's provided component directory.
     */
    public function getComponentDir()
    {
        $config = $this->composer->getConfig();
        return $config->has('door-module-dir') ? $config->get('door-module-dir') : 'modules';
    }	
	
	
    /**
     * Gets the destination Component directory.
     *
     * @return string
     *   The path to where the final Component should be installed.
     */
    public function getComponentPath(PackageInterface $package)
    {
        // Parse the pretty name for the vendor and package name.
        $name = $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        }

        // Allow the component to define its own name.
        $extra = $package->getExtra();
        $component = isset($extra['door-module']) ? $extra['door-module'] : array();
        if (isset($component['name'])) {
            $name = $component['name'];
        }

        // Find where the package should be located.
        return $this->getComponentDir() . DIRECTORY_SEPARATOR . $name;
    }	
	
}
