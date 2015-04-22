<?php
namespace Dkd\CmisService\Resolving;

/**
 * Class ResourceDefinition
 *
 * Defines a Resource consumable by this service, used
 * as "domain transfer object" when linking TYPO3
 * resources to corresponding CMIS resources.
 *
 * Singles out the "page" and "content" types even though
 * those are technically also "record" types. Does so in
 * order to provide an easier API when an implementer
 * desires to integrate with those types specifically.
 *
 * The following standard resource types are included:
 *
 * - page
 * - content
 * - record
 * - file
 * - external
 * - fragment
 *
 * The first three are self-explanatory. The "file" type
 * is used for both FAL relations and legacy file refs.
 * The "external" type is used to indicate a resource that
 * is located elsewhere (as identified by an URI). Finally,
 * the "fragment" type is used when there is no other type
 * which can be applied, for example if someone were to
 * attempt to index an unrecognised relation type.
 *
 * A ResourceTypeDefinition consists of a type and an
 * identifier, for example a "record" type can be identified
 * by the string "tx_my_special_table:123" whereas a "page"
 * type can be identified by just the page UID. Resolvers
 * which receives the ResourceDefinition can then either
 * make a quick decision based on the type, or load the
 * resource for a closer analysis.
 */
class ResourceDefinition {

	const TYPE_PAGE = 'page';
	const TYPE_CONTENT = 'content';
	const TYPE_RECORD = 'record';
	const TYPE_FILE = 'file';
	const TYPE_EXTERNAL = 'external';
	const TYPE_FRAGMENT = 'fragment';

	/**
	 * @var string
	 */
	protected $type = self::TYPE_FRAGMENT;

	/**
	 * @var mixed
	 */
	protected $identifier;

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param mixed $identifier
	 * @return void
	 */
	public function setIdentifier(mixed $identifier) {
		$this->identifier = $identifier;
	}

}
