<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\Media;

trait UrlByRouteSectionsMedia
{
	/**
	 * Return media site version for result URL as media site version param name 
	 * string and media site version param value string. 
	 * 
	 * If media site version is specified in given params array, return this 
	 * media site version. If there is not any specific media site version in 
	 * params array, try to look into given default params array and if there is 
	 * also nothing, use current media site version from router (which could be 
	 * from session or from request). Change params array and add special media 
	 * site version switch param when router is configured to hold media site 
	 * version strictly in session. But do not return any media site version for 
	 * not allowed route methods and do not return any not allowed values for 
	 * media site version.
	 * @param \MvcCore\Route|\MvcCore\IRoute $route 
	 * @param array $params 
	 * @param string|NULL $routeMethod 
	 * @return array `[string $mediaVersionUrlParam, string $mediaSiteUrlValue]`
	 */
	protected function urlByRouteSectionsMedia (\MvcCore\IRoute & $route, array & $params = [], array & $defaultParams = [], $routeMethod = NULL) {
		// separate `$mediaSiteVersion` from `$params` to work with the version more specifically
		$mediaVersionUrlParam = static::URL_PARAM_MEDIA_VERSION;
		if (isset($params[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $params[$mediaVersionUrlParam];
			unset($params[$mediaVersionUrlParam]);
		} else if (isset($defaultParams[$mediaVersionUrlParam])) {
			$mediaSiteVersion = $defaultParams[$mediaVersionUrlParam];
			unset($defaultParams[$mediaVersionUrlParam]);
		} else {
			$mediaSiteVersion = $this->mediaSiteVersion;
		}
		
		// get url version value from application value (only for allowed request types)
		$routeMethod = $route->GetMethod();
		if ($this->routeGetRequestsOnly && $routeMethod !== NULL && $routeMethod !== \MvcCore\IRequest::METHOD_GET) {
			$mediaSiteUrlValue = NULL;
		} else if (isset($this->allowedMediaVersionsAndUrlValues[$mediaSiteVersion])) {
			$mediaSiteUrlValue = $this->allowedMediaVersionsAndUrlValues[$mediaSiteVersion];
		} else {
			$mediaSiteUrlValue = NULL;
			$selfClass = version_compare(PHP_VERSION, '5.5', '>') ? self::class : __CLASS__;
			trigger_error(
				'['.$selfClass.'] Not allowed media site version used to generate url: `'
				.$mediaSiteVersion.'`. Allowed values: `'
				.implode('`, `', array_keys($this->allowedMediaVersionsAndUrlValues)) . '`.',
				E_USER_ERROR
			);
		}
		// add special switching param to global get, if strict session mode and target version is different
		if ($this->stricModeBySession && $mediaSiteVersion !== NULL && $mediaSiteVersion !== $this->mediaSiteVersion) 
			$params[static::URL_PARAM_SWITCH_MEDIA_VERSION] = $mediaSiteVersion;

		return [$mediaVersionUrlParam, $mediaSiteUrlValue];
	}
}
