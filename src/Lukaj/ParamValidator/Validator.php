<?php

namespace Lukaj\ParamValidator;

use Nette;

/**
 * @author Lukas Mazur
 * @license LGPL
 */
class Validator extends \Nette\Object
{
    /** @var array */
    private $options;

    /** @var string */
    private $currentPresenter;

    /** @var string */
    private $currentMethod;

    /** @var Lukaj\Validator\TypeValidators */
    private $typeValidators;

    /** @var array */
    private $rulesNotFound;

    /** @var array */
    private $invalidParams;

    /** @var array */
    private $validParams;

    /**
     * An event which is fired when validation failed, it is fired before throwing
     * @var array
     */
    public $onFail;

    /**
     * Performs a validation and casting
     * @param Nette\Reflection\Method $method
     * @param array $params
     * @return array
     * @throws Lukaj\AutoValidation\RuleNotFoundException if parameter has no validation rule
     * @throws Nette\Application\BadRequestException if validation failed and throwing exceptions is on
     */
    public function combineArgs(Nette\Reflection\Method $method, array $params)
    {
        // when presenter is forwarded old validation output is cleaned
        if ($method->getDeclaringClass()->getName() !== $this->currentPresenter) {
            $this->currentPresenter = $method->getDeclaringClass()->getName();
            $this->rulesNotFound = array();
            $this->invalidParams = array();
            $this->validParams = array();
        }
        $this->currentMethod = $method->getName();

        $rules = Helpers::parseParamAnnotations($method->getAnnotation('param'));

        $retVal = array();
        foreach($method->getParameters() as $rp) {
            $paramName = $rp->getName();
            $value = isset($params[$paramName]) ? $params[$paramName] : NULL;

            if (!empty($rules[$paramName])) {
                if (($validatedType = $this->validateParam($rp, $value, $rules[$paramName])) === NULL) {
                    $this->onFail($paramName, $value, $rp->getDeclaringFunction()->getName(), $rp->getDeclaringClass()->getName());

                    if ($this->options['validationFailed'] === 'exception')    {
                        throw new Nette\Application\BadRequestException('Value ' . ($value !== NULL ? $value : 'NULL') . " is not valid for parameter {$paramName}");
                    } else {
                        $this->invalidParams[$this->currentMethod][$paramName] = $paramName;
                        if ($this->options['validationFailed'] === 'defaultValue') {
                            $value = $rp->isDefaultValueAvailable() && $rp->isOptional() ? $rp->getDefaultValue() : NULL;
                        } elseif($this->options['validationFailed'] === 'null') {
                            $value = NULL;
                        }
                    }
                } else {
                    $this->validParams[$this->currentMethod][$paramName] = $validatedType;
                }
            } else {
                if ($this->options['ruleNotFound'] === 'exception') {
                    throw new RuleNotFoundException("Parameter {$paramName} has no validation rule");
                } else {
                    $this->rulesNotFound[$this->currentMethod][$paramName] = $paramName;
                }
            }

            $retVal[] = $value;
        }

        return $retVal;
    }


    /**
     * Returns true if validation succeeded
     * @return bool
     */
    public function isSuccess()
    {
        if (!isset($this->invalidParams[$this->currentMethod]) || count($this->invalidParams[$this->currentMethod]) === 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * If validation succeeded returns empty array
     * @return array
     */
    public function getInvalidParams()
    {
        return isset($this->invalidParams[$this->currentMethod]) ? $this->invalidParams[$this->currentMethod] : array();
    }

    /**
     * @param Lukaj\ParamValidator\TypeValidators $validators
     * @return void
     */
    public function setTypeValidators(TypeValidators $validators)
    {
        $this->typeValidators = $validators;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Shorcut for integrating to presenter
     * @param string $method
     * @param array $params
     * @param Nette\Application\UI\PresenterComponent $presenter
     * @return bool based on existence of the method
     */
    public function tryCall($method, array $params, Nette\Application\UI\PresenterComponent $presenter)
    {
        $rc = $presenter->getReflection();
        if ($rc->hasMethod($method)) {
            $rm = $rc->getMethod($method);
            if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
                $presenter->checkRequirements($rm);
                $rm->invokeArgs($presenter, $this->combineArgs($rm, $params));
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * @return array
     * @internal
     */
    public function getDebuggingInfo()
    {
        return array('invalidParams' => $this->invalidParams, 'rulesNotFound' => $this->rulesNotFound, 'validParams' => $this->validParams);
    }

    /**
     * Validate single param
     * @param Nette\Reflection\Parameter $param
     * @param string $value a value of param
     * @param array $rules An array containing parsed annotations
     * @return string|NULL
     */
    private function validateParam(\Nette\Reflection\Parameter $param, &$value, array $rules)
    {
        // NULL requires special handling
        if ($value === NULL) {
            if ($param->isDefaultValueAvailable() && $param->isOptional()) {
                $value = $param->getDefaultValue();
                return 'null';
            } elseif (in_array('null', array_map('strtolower', $rules['type']))) {
                return 'null';
            } else {
                return NULL;
            }
        }

        foreach($rules['type'] as $type) {
            $normalizedType = strtolower(str_replace('\\', '_', (substr($type, 0, 1) === '\\' ? substr($type, 1) : $type)));

            if ($this->typeValidators->{"_$normalizedType"}($value, array_slice($rules, 1))) {
                return $type;
            }
        }
        return NULL;
    }
}