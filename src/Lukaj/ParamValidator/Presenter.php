<?php

namespace Lukaj\ParamValidator;

use Nette;

/**
 * @author Lukas Mazur
 * @license LGPL
 */
class Presenter extends Nette\Application\UI\Presenter
{
	/** @var Lukaj\ParamValidator\Validator */
	protected $paramValidator;
	
	/**
	 * @param Lukaj\ParamValidator\Validator $validator
	 * @return void
	 */
	public function injectParamValidator(Validator $validator)
	{
		$this->paramValidator = $validator;
	}	
	
	/**
	 * Calls public method if exists.
	 * @param  string $method
	 * @param  array $params
	 * @return bool  does method exist?
	 */
	protected function tryCall($method, array $params)
	{
		$rc = $this->getReflection();
		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);
			if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
				$this->checkRequirements($rm);
				$rm->invokeArgs($this, $this->paramValidator->combineArgs($rm, $params));
				return TRUE;
			}
		}
		return FALSE;
	}	
}
