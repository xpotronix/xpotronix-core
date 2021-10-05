<?php

declare(strict_types=1);

namespace Xpotronix\Glade;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;

class Imageblob
{
    /**
     */
    private $obj;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        array $config = [],
        Object $obj
    ) {
        $this->config = new Config($config);
        $this->obj = $obj;
    }

    public function fileExists(string $location): bool
    {
        return true;
    }

    public function read(string $location): string
	{
		$image_field = $this->config->get('image_field');
        return $this->obj->$image_field;
    }

}
