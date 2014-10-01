<?php

namespace Lukaj\ParamValidator\DI;

use Nette;

if (!class_exists('Nette\DI\CompilerExtension')) {
    class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
    class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
}
if (!class_exists('Nette\Configurator')) {
    class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Lukas Mazur
 * @license LGPL
 * @internal
 */
class ParamValidatorExtension extends Nette\DI\CompilerExtension
{
    public $defaultOptions = array(
            'ruleNotFound' => 'silent',
            'validationFailed' => 'defaultValue',
            'typeValidators' => 'Lukaj\ParamValidator\TypeValidators'
        );

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaultOptions);
        $builder = $this->getContainerBuilder();

        $service = $builder->addDefinition($this->prefix('paramValidator'))
                ->setClass('Lukaj\ParamValidator\Validator');
        if (isset($config['typeValidators'])) {
            $service->addSetup('setTypeValidators', array(new Nette\DI\Statement('Lukaj\ParamValidator\TypeValidators')));
            unset($config['typeValidators']);
        }
        if (!empty($config)) {
            $service->addSetup('setOptions', array($config));
        }
    }

    /**
     * Workaround for Nette 2.0
     * @param Nette\Configurator $configurator
     * @return void
     */
    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($configurator, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('paramValidator', new ParamValidator);
        };
    }
}
