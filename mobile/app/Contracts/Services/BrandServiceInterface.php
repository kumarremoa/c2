<?php

namespace App\Contracts\Services;

interface BrandServiceInterface
{
	public function create();

	public function get();

	public function update();

	public function delete();

	public function search();
}


?>
