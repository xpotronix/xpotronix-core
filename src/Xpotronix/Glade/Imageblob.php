<?php

declare(strict_types=1);

namespace Xpotronix\Glade;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;

class Imageblob implements FilesystemAdapter
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

		M()->user( 'en read' );

		$image_field = $this->config->get('image_field');
        return $this->obj->$image_field;
    }

}
