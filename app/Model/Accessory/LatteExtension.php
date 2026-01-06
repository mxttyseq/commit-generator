<?php

declare(strict_types=1);

namespace App\Model\Accessory;

use Latte\Extension;


final class LatteExtension extends Extension
{
	public function getFilters(): array
	{
		return [];
	}


	public function getFunctions(): array
	{
		return [];
	}
}
