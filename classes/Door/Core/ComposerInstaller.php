<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */

namespace Door\Core;
use Composer\Installer\InstallerInterface;
use Composer\Package\PackageInterface;

/**
 * Description of Installer
 *
 * @author serginho
 */
class ComposerInstaller extends InstallerInterface{
	
    public function supports($packageType)
    {
        return $packageType === 'door-module';
    }	
	
    /**
     * Retrieves the Installer's provided component directory.
     */
    public function getInstallDir()
    {
        $config = $this->composer->getConfig();
        return $config->has('door-module-dir') ? $config->get('door-module-dir') : 'modules';
    }	
	
    public function getInstallPath(PackageInterface $package, $frameworkType = '')
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
