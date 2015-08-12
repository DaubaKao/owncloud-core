<?php
/**
 * @author Robin McCorkell <rmccorkell@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_External\Lib\Auth;

use \OCA\Files_External\Lib\StorageConfig;
use \OCA\Files_External\Lib\VisibilityTrait;
use \OCA\Files_External\Lib\IdentifierTrait;
use \OCA\Files_External\Lib\FrontendDefinitionTrait;
use \OCA\Files_External\Lib\StorageModifierTrait;

/**
 * Authentication mechanism
 */
class AuthMechanism implements \JsonSerializable {

	/** Standard authentication schemes */
	const SCHEME_NULL = 'null';
	const SCHEME_BUILTIN = 'builtin';
	const SCHEME_PASSWORD = 'password';
	const SCHEME_OAUTH1 = 'oauth1';
	const SCHEME_OAUTH2 = 'oauth2';
	const SCHEME_PUBLICKEY = 'publickey';
	const SCHEME_OPENSTACK = 'openstack';

	use VisibilityTrait;
	use FrontendDefinitionTrait;
	use StorageModifierTrait;
	use IdentifierTrait;

	/** @var string */
	protected $scheme;

	/**
	 * Get the authentication scheme implemented
	 * See self::SCHEME_* constants
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @param string $scheme
	 * @return self
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * Serialize into JSON for client-side JS
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$data = $this->jsonSerializeDefinition();
		$data['scheme'] = $this->getScheme();

		return $data;
	}

	/**
	 * Check if parameters are satisfied in a StorageConfig
	 *
	 * @param StorageConfig $storage
	 * @return bool
	 */
	public function validateStorage(StorageConfig $storage) {
		// does the backend actually support this scheme
		$supportedSchemes = $storage->getBackend()->getAuthSchemes();
		if (!isset($supportedSchemes[$this->getScheme()])) {
			return false;
		}

		return $this->validateStorageDefinition($storage);
	}

}
