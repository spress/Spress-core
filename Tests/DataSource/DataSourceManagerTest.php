<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\Tests\DataSource;

use PHPUnit\Framework\TestCase;
use Yosymfony\Spress\Core\DataSource\DataSourceManager;
use Yosymfony\Spress\Core\DataSource\Filesystem\FilesystemDataSource;

class DataSourceManagerTest extends TestCase
{
    public function testCreatingDataSourceManager()
    {
        $dsm = new DataSourceManager();

        $this->assertTrue(is_array($dsm->getItems()));
        $this->assertTrue(is_array($dsm->getLayouts()));
        $this->assertTrue(is_array($dsm->getIncludes()));

        $this->assertCount(0, $dsm->getItems());
        $this->assertCount(0, $dsm->getLayouts());
        $this->assertCount(0, $dsm->getIncludes());
    }

    public function testAddDataSource()
    {
        $dsm = new DataSourceManager();
        $fsDataSource = new FilesystemDataSource([
            'source_root' => __dir__.'/../fixtures/project/src',
            'layouts_root' => __dir__.'/../fixtures/project/src/layouts',
            'includes_root' => __dir__.'/../fixtures/project/src/includes',
            'posts_root' => __dir__.'/../fixtures/project/src/content/posts',
            'text_extensions' => ['htm', 'html', 'md', 'mkd', 'xml'],
        ]);

        $dsm->addDataSource('filesystem', $fsDataSource);

        $this->assertTrue($dsm->hasDataSource('filesystem'));

        $dsm->load();

        $this->assertCount(15, $dsm->getItems());
        $this->assertCount(1, $dsm->getLayouts());
        $this->assertCount(1, $dsm->getIncludes());
    }

    public function testReLoad()
    {
        $dsm = new DataSourceManager();
        $fsDataSource = new FilesystemDataSource([
            'source_root' => __dir__.'/../fixtures/project/src',
            'text_extensions' => ['htm', 'html', 'md', 'mkd', 'xml'],
        ]);

        $dsm->addDataSource('filesystem', $fsDataSource);
        $dsm->load();
        $dsm->load();

        $this->assertCount(15, $dsm->getItems());
        $this->assertCount(1, $dsm->getLayouts());
        $this->assertCount(1, $dsm->getIncludes());
    }

    public function testRemoveDataSource()
    {
        $dsm = new DataSourceManager();
        $fsDataSource1 = new FilesystemDataSource([
            'source_root' => __dir__.'/../fixtures/project/src',
            'text_extensions' => ['htm', 'html', 'md', 'mkd', 'xml'],
        ]);

        $fsDataSource2 = new FilesystemDataSource([
            'source_root' => __dir__.'/../fixtures/project/src',
            'text_extensions' => ['htm', 'html', 'md', 'mkd', 'xml'],
        ]);

        $dsm->addDataSource('filesystem_1', $fsDataSource1);
        $dsm->addDataSource('filesystem_2', $fsDataSource2);
        $dsm->removeDataSource('filesystem_1');
        $dsm->load();

        $this->assertCount(1, $dsm->getDataSourceNames());
        $this->assertContains('filesystem_2', $dsm->getDataSourceNames());
        $this->assertNotContains('filesystem_1', $dsm->getDataSourceNames());

        $this->assertCount(15, $dsm->getItems());
        $this->assertCount(1, $dsm->getLayouts());
        $this->assertCount(1, $dsm->getIncludes());
    }

    public function testGetDatasource()
    {
        $dsm = new DataSourceManager();
        $fsDataSource1 = new FilesystemDataSource([
            'source_root' => __dir__.'/../fixtures/project/src',
            'text_extensions' => ['htm', 'html', 'md', 'mkd', 'xml'],
        ]);

        $dsm->addDataSource('filesystem_1', $fsDataSource1);

        $this->assertInstanceOf('\Yosymfony\Spress\Core\DataSource\AbstractDataSource', $dsm->getDataSource('filesystem_1'));
    }

    public function testNotHasDataSource()
    {
        $dsm = new DataSourceManager();

        $this->assertFalse($dsm->hasDataSource('filesystem'));
    }
}
