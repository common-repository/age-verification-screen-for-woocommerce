<?php namespace AlpakaWP\AgeVerification\Admin;

use AlpakaWP\AgeVerification\Core\ServiceContainerTrait;

/**
 * Class Admin
 *
 * @package AlpakaWP\AgeVerification\Admin
 */
class Admin {

	use ServiceContainerTrait;

	/**
	 * Admin constructor.
	 *
	 * Register menu items and handlers
	 */
	public function __construct() {

		$this->getContainer()->add( 'postRestrictionManager', new PostRestrictionManager() );
		$this->getContainer()->add( 'taxonomyRestrictionManager', new TaxonomyRestrictionManager() );

		if ( get_transient( 'woocommerce_age_verification_activated' ) ) {
			add_action( 'admin_notices', [ $this, 'showActivationMessage' ] );
		}
	}

	/**
	 * Show message about activation plugin and advise next step
	 */
	public function showActivationMessage() {
		$link = $this->getContainer()->getSettings()->getLink();
		$this->getContainer()->getFileManager()->includeTemplate( 'admin/alerts/activation-alert.php', [ 'link' => $link ] );

		delete_transient( 'woocommerce_age_verification_activated' );
	}

}