<?php

namespace Lukaj\ParamValidator;

/**
 * @author Lukas Mazur
 * @license LGPL
 */
class  Helpers
{
	/**
	 * @param array|string $annotations
	 * @return array
	 */
	public static function parseParamAnnotations($annotations)
	{
		if (!is_array($annotations)) {
			$annotations = array($annotations);
		}
		
		$result = array();		
		foreach($annotations as $value)
		{
			$parsed = explode(' ', $value);
			if (count($parsed) < 2) { // annotation is not formed properly
				continue;
			}
			$name = substr($parsed[1], 0, 1) === '$' ? substr($parsed[1], 1) : $parsed[1];
			$result[$name] = array('type' => explode('|', $parsed[0]));
			foreach(array_slice($parsed, 2) as $param)
			{
				$row = explode(':', $param);
				$result[$name][$row[0]] = isset($row[1]) ? $row[1] : NULL;
			}
		}
		
		return $result;
	}
}
