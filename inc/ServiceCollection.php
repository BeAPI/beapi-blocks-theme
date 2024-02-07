<?php

namespace BEA\Theme\Framework;

final class ServiceCollection {

	private $services = [];

	/**
	 * Register a new service.
	 *
	 * @param Service $service
	 *
	 * @return self
	 */
	public function add( Service $service ): self {
		$this->services[ spl_object_hash( $service ) ] = $service;

		return $this;
	}

	/**
	 * Get all registered services.
	 *
	 * @return Service[]
	 */
	public function all(): array {
		return $this->services;
	}

	/**
	 * Return a new collection.
	 *
	 * @param callable $predicate
	 * @param ...$args
	 *
	 * @return ServiceCollection
	 */
	public function filter( callable $predicate, ...$args ): ServiceCollection {
		$new_collection = new self();

		foreach ( $this->services as $service ) {
			if ( $predicate( $service, ...$args ) ) {
				$new_collection->add( $service );
			}
		}

		return $new_collection;
	}
}