<?php namespace AlpakaWP\AgeVerification\Core;

trait ServiceContainerTrait {

	public function getContainer() {
		return ServiceContainer::getInstance();
	}

}
