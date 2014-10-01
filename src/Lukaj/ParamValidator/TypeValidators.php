<?php

namespace Lukaj\ParamValidator;

use Nette;

/**
 * @author Lukas Mazur
 * @license LGPL
 */
class TypeValidators
{
    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _int(&$param, array $rules)
    {
        if (!((string)(int)$param === $param)) {
            return FALSE;
        }

        foreach($rules as $key => $value) {
            switch($key) {
                case 'min':    {
                    if ($param < $value) {
                        return FALSE;
                    }
                    break;
                }
                case 'max': {
                    if ($param > $value) {
                        return FALSE;
                    }
                    break;
                }
            }
        }

        settype($param, 'int');

        return TRUE;
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _integer(&$param, array $rules) {
        return $this->_int($param, $rules);
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _float(&$param, array $rules)
    {
        if (!is_numeric($param)) {
            return FALSE;
        }

        foreach($rules as $key => $value) {
            switch($key) {
                case 'min':
                    if ($param < $value) {
                        return FALSE;
                    }
                    break;
                case 'max':
                    if ($param > $value) {
                        return FALSE;
                    }
                    break;
            }
        }

        settype($param, 'float');
        return TRUE;
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _double(&$param, array $rules)
    {
        return $this->_float($param, $rules);
    }

    /**
     * Empty string is considered as NULL
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _string($param, array $rules)
    {
        if (!is_string($param) || empty($param)) {
            return FALSE;
        }

        foreach($rules as $key => $value) {
            switch($key) {
                case 'regexp':
                    return (bool)preg_match("/{$value}/", $param);
            }
        }

        return TRUE;
    }

    /**
     * Only 0 and 1 are allowed
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _bool(&$param, array $rules)
    {
        if ($param == '1' || $param == '0') {// == intentionally
            settype($param, 'bool');
            return TRUE;
        } else     {
            return FALSE;
        }
    }

    /**
     * Only 0 and 1 are allowed
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _boolean(&$param, array $rules)
    {
        return $this->_bool($param, $rules);
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _true(&$param, array $rules)
    {
        if ($param == '1') {// == intentionally
            $param = TRUE;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _false(&$param, array $rules)
    {
        if ($param == '0') { // == intentionally
            $param = FALSE;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _array($param, array $rules)
    {
        if (is_array($param)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Performs no validation, everytime returns TRUE
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return TRUE
     */
    public function _mixed($param, array $rules)
    {
        return TRUE;
    }

    /**
     * NULL requires special handling
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return FALSE
     */
    public function _null($param, array $rules)
    {
        return FALSE;
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _nette_datetime(&$param, array $rules)
    {
        try {
            $param = Nette\DateTime::from($param);
        } catch (\Exception $e) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _datetime(&$param, array $rules)
    {
        try {
            $param = new \DateTime($param);
        } catch (\Exception $e) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $param value of param
     * @param array $rules array of rules name => value
     * @return bool
     */
    public function _datetimeimmutable(&$param, array $rules)
    {
        try {
            $param = new \DateTimeImmutable($param);
        } catch (\Exception $e) {
            return FALSE;
        }

        return TRUE;
    }
}
