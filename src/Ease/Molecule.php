<?php
/**
 * Something between Atom and Sand
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2017 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Description of Molecule
 *
 * @author vitex
 */
class Molecule extends Atom
{
    /**
     * Object Specific Configurations
     * @var array
     */
    public $configuration = [];

    /**
     * Load Configuration values from json file $this->configFile and define UPPERCASE keys
     */
    public function loadConfig($configFile)
    {
        if (!file_exists($configFile)) {
            throw new Exception('Config file '.realpath($configFile) ? realpath($configFile)
                        : $configFile.' does not exist');
        }
        $this->shared        = \Ease\Shared::instanced();
        $this->configuration = json_decode(file_get_contents($configFile), true);
        if (is_null($this->configuration)) {
            $this->addStatusMessage('Empty Config File '.realpath($configFile) ? realpath($configFile)
                        : $configFile, 'debug');
        } else {
            foreach ($this->configuration as $configKey => $configValue) {
                if ((strtoupper($configKey) == $configKey) && (!defined($configKey))) {
                    define($configKey, $configValue);
                } else {
                    $this->shared->setConfigValue($configKey, $configValue);
                }
            }
        }

        if (array_key_exists('debug', $this->configuration)) {
            $this->debug = boolval($this->configuration['debug']);
        }
    }

    /**
     * Set up one of properties
     *
     * @param array  $options  array of given properties
     * @param string $name     name of property to process
     * @param string $constant load default property value from constant
     */
    public function setupProperty($options, $name, $constant = null)
    {
        if (isset($options[$name])) {
            $this->$name = $options[$name];
        } else {
            if (is_null($this->$name) && !empty($constant) && defined($constant)) {
                $this->$name = constant($constant);
            }
        }
    }
}