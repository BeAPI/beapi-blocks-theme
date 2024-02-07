<?php

namespace BEA\Theme\Framework;

class Asset {

	private $asset_type;
	private $handle;
	private $src;
	private $dependencies;
	private $version;

	public function __construct( string $asset_type, string $handle, string $src = '', array $dependencies = [], string $version = '' ) {
		$this->asset_type   = $asset_type;
		$this->handle       = $handle;
		$this->src          = $src;
		$this->dependencies = $dependencies;
		$this->version      = $version;
	}

	public function get_asset_type(): string {
		return $this->asset_type;
	}

	public function get_handle(): string {
		return $this->handle;
	}

	public function get_src(): string {
		return $this->src;
	}

	public function get_dependencies(): array {
		return $this->dependencies;
	}

	public function get_version(): string {
		return $this->version;
	}
}