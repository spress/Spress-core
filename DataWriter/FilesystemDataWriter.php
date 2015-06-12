<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\DataWriter;

use Symfony\Component\Filesystem\Filesystem;
use Yosymfony\Spress\Core\DataSource\ItemInterface;

/**
 * File data writer.
 *
 * This data writer uses SNAPSHOT_PATH_PERMALINK for working
 * with the path of the items. In case of binary item this data writer
 * uses SNAPSHOT_PATH_SOURCE and SNAPSHOT_PATH_RELATIVE.
 *
 * @author Victor Puertas <vpgugr@gmail.com>
 */
class FilesystemDataWriter implements DataWriterInterface
{
    protected $filesystem;
    protected $outputDir;

    /**
     * Constructor.
     *
     * @param Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                  $outputDir  The output folder. e.g: "_site"
     */
    public function __construct(Filesystem $filesystem, $outputDir)
    {
        $this->filesystem = $filesystem;
        $this->outputDir = $outputDir;
    }

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->filesystem->remove($this->outputDir);
    }

    /**
     * @inheritDoc
     */
    public function write(ItemInterface $item)
    {
        if ($this->isWritable($item) === false) {
            return;
        }

        if ($item->isBinary() === true) {
            $sourcePath = $item->getPath(ItemInterface::SNAPSHOT_PATH_SOURCE);
            $outputPath = $item->getPath(ItemInterface::SNAPSHOT_PATH_RELATIVE);

            if (strlen($sourcePath) > 0) {
                $this->copy($sourcePath, $this->composeOutputPath($outputPath));
            } else {
                $this->filesystem->dumpFile($this->composeOutputPath($outputPath), $item->getContent());
            }
        }

        $outputPath = $item->getPath(ItemInterface::SNAPSHOT_PATH_PERMALINK);

        if (strlen($outputPath) == 0) {
            return;
        }

        $this->filesystem->dumpFile($this->composeOutputPath($outputPath), $item->getContent());
    }

    /**
     * @inheritDoc
     */
    public function tearDown()
    {
    }

    protected function composeOutputPath($relativePath)
    {
        $path = $this->outputDir.'/'.$relativePath;

        return str_replace('//', '/', $path);
    }

    protected function isWritable(ItemInterface $item)
    {
        return $item->getPath(ItemInterface::SNAPSHOT_PATH_RELATIVE) === '' ? false : true;
    }
}
